<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Domains\Seo\SeoPolicy;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class AdController extends Controller { public function show(Request $request, string $ad, string $slug, SeoPolicy $seo): View|RedirectResponse { $listing=Ad::query()->publiclyVisible()->where('code',$ad)->with(['images','category','city','province','country','links'])->firstOrFail(); if ($listing->slug !== $slug) return redirect()->to($listing->publicUrl(),301); $viewKey='ad-viewed-'.$listing->id; if (!$request->session()->has($viewKey)) { $listing->increment('views_count'); $request->session()->put($viewKey,true); } $related=Ad::query()->publiclyVisible()->whereKeyNot($listing->id)->where('category_id',$listing->category_id)->when($listing->city_id,fn($q)=>$q->where('city_id',$listing->city_id))->with(['city','images'])->orderedForListing()->limit(6)->get(); return view('public.ads.show',['ad'=>$listing,'related'=>$related,'seo'=>$seo]); } }
