<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\PendingAd;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DomainException;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $term   = $request->string('q')->trim()->toString();
        $type   = $request->string('type')->toString();

        // Fetch registered ads
        $adsQuery = Ad::query()
            ->with(['user', 'city', 'category'])
            ->when($status && !in_array($status, ['pending', 'expired_pending'], true), fn ($q) => $q->where('status', $status))
            ->when($term, fn ($q) => $q->where(fn ($q) => $q
                ->where('code', 'like', $term.'%')
                ->orWhere('mobile_1', 'like', $term.'%')
                ->orWhere('title', 'like', '%'.$term.'%')))
            ->when($type === 'registered', fn ($q) => $q->where('source', 'user_panel'))
            ->when($type === 'guest', fn ($q) => $q->where('source', 'guest'));

        // Fetch pending (failed) ads
        $pendingQuery = PendingAd::query()
            ->when($status === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($status === 'expired_pending', fn ($q) => $q->where('status', 'expired'))
            ->when(in_array($status, ['active', 'inactive', 'deleted', 'draft', 'needs_permit', 'pending_payment', 'pending_approval'], true), fn ($q) => $q->whereRaw('0=1'))
            ->when($type === 'registered', fn ($q) => $q->whereRaw('0=1'))
            ->when($term, fn ($q) => $q->where(fn ($q) => $q
                ->whereRaw("json_extract(ad_data, '$.title') like ?", ['%'.$term.'%'])
                ->orWhereRaw("json_extract(ad_data, '$.mobile_1') like ?", [$term.'%'])));

        $ads = $adsQuery->latest()->get()->map(fn ($ad) => [
            'type' => 'registered',
            'model' => $ad,
            'created_at' => $ad->created_at,
            'updated_at' => $ad->updated_at,
            'title' => $ad->title,
            'code' => $ad->code,
            'mobile' => $ad->user->mobile ?? '—',
            'city' => $ad->city?->name ?? '—',
            'views_count' => $ad->views_count,
            'status' => $ad->status->value,
            'status_label' => $ad->status->label(),
            'status_class' => match($ad->status) {
                \App\Domains\Ads\Enums\AdStatus::Active => 'badge-success',
                \App\Domains\Ads\Enums\AdStatus::Expired, \App\Domains\Ads\Enums\AdStatus::Deleted => 'badge-danger',
                default => '',
            },
        ]);

        $pendingAds = $pendingQuery->latest()->get()->map(fn ($pending) => [
            'type' => 'pending',
            'model' => $pending,
            'created_at' => $pending->created_at,
            'updated_at' => null,
            'title' => $pending->ad_data['title'] ?? '—',
            'code' => null,
            'mobile' => $pending->ad_data['mobile_1'] ?? '—',
            'city' => \App\Models\City::find($pending->ad_data['city_id'])->name ?? '—',
            'views_count' => null,
            'status' => $pending->status,
            'status_label' => match($pending->status) {
                'pending' => 'در انتظار',
                'completed' => 'تکمیل شده',
                'expired' => 'منقضی شده',
                default => $pending->status,
            },
            'status_class' => match($pending->status) {
                'completed' => 'badge-success',
                'expired' => 'badge-danger',
                default => 'badge-warning',
            },
        ]);

        $merged = $ads->merge($pendingAds)->sortByDesc('created_at')->values();

        $currentPage = $request->integer('page', 1);
        $perPage = 15;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->slice(($currentPage - 1) * $perPage, $perPage),
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.ads.index', compact('paginated', 'status', 'term', 'type'));
    }

    public function transition(Request $request, Ad $ad, AdWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_map(
                fn ($status) => $status->value, AdStatus::cases()))],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $workflow->transition($ad, AdStatus::from($data['status']), $request->user(), $data['reason'] ?? null);
        } catch (DomainException) {
            return back()->withErrors(['status' => 'تغییر وضعیت درخواستی مجاز نیست.']);
        }

        return back()->with('success', 'وضعیت آگهی به‌روزرسانی شد.');
    }

    public function finalizePending(Request $request, PendingAd $pending, AdSubmissionService $service): RedirectResponse
    {
        $data = $pending->ad_data;
        $imagePaths = $data['_images'] ?? [];
        $services = $data['_services'] ?? [];

        unset($data['_images'], $data['_services'], $data['_payment_type'], $data['_permit_image']);

        $admin = $request->user();

        $ad = $service->create($admin, $data, [], $request->ip());

        foreach ($imagePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $file = new \Illuminate\Http\UploadedFile(
                    storage_path('app/public/' . $path),
                    basename($path),
                    mime_content_type(storage_path('app/public/' . $path)),
                    null,
                    true
                );
                $service->update($ad, $admin, [], [$file]);
            }
        }

        if (!empty($services)) {
            foreach ($services as $tariffId) {
                $tariff = \App\Models\Tariff::find($tariffId);
                if ($tariff && $tariff->is_active) {
                    \App\Models\AdService::create([
                        'ad_id' => $ad->id,
                        'tariff_id' => $tariff->id,
                        'starts_at' => now(),
                        'expires_at' => $tariff->duration_days ? now()->addDays($tariff->duration_days) : null,
                        'status' => 'active',
                    ]);
                }
            }
        }

        $pending->update(['status' => 'completed']);

        return back()->with('success', 'آگهی ناموفق با کد ' . $ad->code . ' ثبت شد.');
    }

    public function destroyPending(PendingAd $pending): RedirectResponse
    {
        $token = $pending->token;

        $paths = $pending->ad_data['_images'] ?? [];
        foreach ($paths as $path) {
            Storage::disk('public')->delete($path);
        }

        $permitPath = $pending->ad_data['_permit_image'] ?? null;
        if ($permitPath) {
            Storage::disk('public')->delete($permitPath);
        }

        Storage::disk('public')->deleteDirectory('pending-ads/' . $token);

        $pending->delete();

        return back()->with('success', 'آگهی ناموفق حذف شد.');
    }

    public function updatePendingStatus(Request $request, PendingAd $pending): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,completed,expired'],
        ]);

        $pending->update(['status' => $data['status']]);

        return back()->with('success', 'وضعیت آگهی ناموفق به‌روزرسانی شد.');
    }
}
