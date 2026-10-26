<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\View\View;
class DashboardController extends Controller { public function __invoke(Request $request): View { $status=$request->string('status')->toString(); $ads=Ad::query()->where('user_id',$request->user()->id)->with(['city','images'])->when($status,fn($q)=>$q->where('status',$status))->latest()->paginate(20)->withQueryString(); return view('user.dashboard',compact('ads','status')); } }
