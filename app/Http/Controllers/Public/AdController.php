<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Domains\Seo\SeoPolicy;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AdController extends Controller {
    /**
     * PRD §69: 404/410 handling for ad pages.
     *  - unknown code or soft-deleted ad          -> 404
     *  - expired ad (status = Expired)            -> 410 (branded error page, no ad content)
     *  - any other non-public status (draft, pending, rejected, ...) -> 404 (never leak existence)
     */
    public function show(Request $request, string $ad, string $slug, SeoPolicy $seo): View|RedirectResponse {
        $listing = Ad::query()->where('code', $ad)->with(['images', 'category', 'city', 'province', 'country', 'links'])->first();
        if (!$listing || $listing->deleted_at !== null) abort(404);
        if ($listing->status === \App\Domains\Ads\Enums\AdStatus::Expired) abort(410);
        if (!$seo->isIndexableAd($listing)) abort(404);
        if ($listing->slug !== $slug) return redirect()->to($listing->publicUrl(), 301);
        $viewKey='ad-viewed-'.$listing->id;
        if (!$request->session()->has($viewKey)) { $listing->increment('views_count'); $request->session()->put($viewKey,true); }
        $related=Ad::query()->publiclyVisible()->whereKeyNot($listing->id)->where('category_id',$listing->category_id)->when($listing->city_id,fn($q)=>$q->where('city_id',$listing->city_id))->with(['city','images'])->orderedForListing()->limit(6)->get();
        return view('public.ads.show',['ad'=>$listing,'related'=>$related,'seo'=>$seo]);
    }

    /**
     * PRD §38: deep ad URL inside the city/category path
     * (e.g. /tehran/industrial/cnc/{slug}/ad/{code}) - 301 to the canonical
     * short URL /ad/{code}/{slug} so there is never duplicate content.
     */
    public function deepAd(Request $request): RedirectResponse {
        // NB: scalar params arrive positionally (pattern order), so we read
        // them by name off the route instead of a bare signature.
        $code = (string) $request->route('code');
        $listing = Ad::query()->where('code', $code)->first();
        if (!$listing || $listing->deleted_at !== null) abort(404);
        return redirect()->to($listing->publicUrl(), 301);
    }
}
