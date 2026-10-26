<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdPermit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class PermitController extends Controller { public function store(Request $request,Ad $ad): RedirectResponse { $this->authorize('view',$ad); abort_unless($ad->status===AdStatus::NeedsPermit,422); $data=$request->validate(['permit_number'=>['nullable','string','max:160'],'issuer'=>['nullable','string','max:190'],'issued_at'=>['nullable','date'],'image'=>['nullable','image','max:5120']]); $path=$request->file('image')?->store('permits','local'); AdPermit::query()->create([...$data,'ad_id'=>$ad->id,'image_path'=>$path,'status'=>'pending']); return back()->with('success','مجوز برای بررسی ارسال شد.'); } }
