<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class AdReportController extends Controller { public function store(Request $request,string $ad): RedirectResponse { $listing=Ad::query()->publiclyVisible()->where('code',$ad)->firstOrFail(); $data=$request->validate(['reason'=>['required','in:no_response,fraud,false_information,illegal,inappropriate,overpriced,other'],'description'=>['nullable','string','max:2000']]); AdReport::query()->create([...$data,'ad_id'=>$listing->id,'reporter_user_id'=>$request->user()?->id,'reporter_ip'=>$request->ip(),'status'=>'new']); return back()->with('success','گزارش شما ثبت شد و بررسی خواهد شد.'); } }
