<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ModerationController extends Controller { public function index(Request $request): View { $status=$request->string('status')->toString(); $term=$request->string('q')->trim()->toString(); $ads=Ad::query()->with(['user','city','category'])->when($status,fn($q)=>$q->where('status',$status))->when($term,fn($q)=>$q->where(fn($q)=>$q->where('code','like',$term.'%')->orWhere('mobile_1','like',$term.'%')->orWhere('title','like','%'.$term.'%')))->latest()->paginate(50)->withQueryString(); return view('admin.ads.index',compact('ads','status','term')); } public function transition(Request $request, Ad $ad, AdWorkflow $workflow): RedirectResponse { $data=$request->validate(['status'=>['required','in:'.implode(',',array_map(fn($status)=>$status->value,AdStatus::cases()))],'reason'=>['nullable','string','max:1000']]); $workflow->transition($ad,AdStatus::from($data['status']),$request->user(),$data['reason']??null); return back()->with('success','وضعیت آگهی به‌روزرسانی شد.'); } }
