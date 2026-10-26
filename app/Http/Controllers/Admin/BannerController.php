<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\HomeBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $setting = SiteSetting::get(HomeBanner::SETTING_KEY);
        $banner = is_array($setting) ? $setting : null;
        $bannerUrl = (is_array($setting) && isset($setting['path']))
            ? Storage::disk('public')->url($setting['path'])
            : null;

        return view('admin.banner.index', ['banner' => $banner, 'bannerUrl' => $bannerUrl]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp,svg,html,htm'],
        ]);

        $file = $validated['file'];
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === 'htm') {
            $extension = 'html';
        }
        $type = in_array($extension, ['html'], true) ? 'html' : 'image';

        $path = $file->storeAs('banners', 'home-banner-'.now()->format('YmdHis').'.'.$extension, 'public');

        SiteSetting::put(HomeBanner::SETTING_KEY, [
            'type' => $type,
            'path' => $path,
            'is_active' => true,
        ]);
        HomeBanner::forget();

        return redirect()->route('admin.banner.index')->with('success', 'بنر بارگذاری و فعال شد.');
    }

    public function toggle(): RedirectResponse
    {
        $setting = SiteSetting::get(HomeBanner::SETTING_KEY);

        if (is_array($setting)) {
            $setting['is_active'] = !($setting['is_active'] ?? false);
            SiteSetting::put(HomeBanner::SETTING_KEY, $setting);
            HomeBanner::forget();
        }

        return back();
    }

    public function destroy(): RedirectResponse
    {
        $setting = SiteSetting::get(HomeBanner::SETTING_KEY);

        if (is_array($setting) && isset($setting['path'])) {
            Storage::disk('public')->delete($setting['path']);
        }

        SiteSetting::forget(HomeBanner::SETTING_KEY);
        HomeBanner::forget();

        return redirect()->route('admin.banner.index')->with('success', 'بنر حذف شد.');
    }
}
