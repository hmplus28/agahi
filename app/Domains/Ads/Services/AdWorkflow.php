<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\AdStatusHistory;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class AdWorkflow
{
    /** @var array<string, list<AdStatus>> */
    private const TRANSITIONS = [
        'draft' => [AdStatus::PendingApproval, AdStatus::Deleted],
        'pending_approval' => [AdStatus::Active, AdStatus::NeedsPermit, AdStatus::Inactive, AdStatus::Deleted],
        'active' => [AdStatus::PendingApproval, AdStatus::Inactive, AdStatus::Expired, AdStatus::Deleted],
        'needs_permit' => [AdStatus::PendingApproval, AdStatus::Active, AdStatus::Inactive, AdStatus::Deleted],
        'inactive' => [AdStatus::PendingApproval, AdStatus::Active, AdStatus::Deleted],
        'expired' => [AdStatus::Active, AdStatus::Deleted],
        'deleted' => [AdStatus::Inactive],
    ];

    public function transition(Ad $ad, AdStatus $target, ?User $actor = null, ?string $reason = null): Ad
    {
        return DB::transaction(function () use ($ad, $target, $actor, $reason): Ad {
            $locked = Ad::query()->lockForUpdate()->findOrFail($ad->id);
            $from = $locked->status;
            if ($from === $target) return $locked;
            if (!in_array($target, self::TRANSITIONS[$from->value] ?? [], true)) {
                throw new DomainException('تغییر وضعیت درخواستی مجاز نیست.');
            }
            $attributes = ['status' => $target];
            if ($target === AdStatus::Active) {
                $attributes['published_at'] ??= $locked->published_at ?? now();
                $attributes['sort_at'] = now();
                $attributes['expires_at'] ??= $locked->expires_at ?? now()->addDays((int) config('agahi.free_ad_duration_days', 30));
                $attributes['deleted_at'] = null;
            }
            if ($target === AdStatus::Expired) $attributes['expires_at'] = now();
            if ($target === AdStatus::Deleted) $attributes['deleted_at'] = now();
            $locked->forceFill($attributes)->save();
            AdStatusHistory::query()->create(['ad_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'changed_by' => $actor?->id, 'reason' => $reason, 'created_at' => now()]);
            Cache::forget('seo.sitemap.pages.v1');
            return $locked->refresh();
        });
    }
}
