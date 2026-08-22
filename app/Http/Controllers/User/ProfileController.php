<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\City;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class ProfileController extends Controller { public function edit(): View { return view('user.profile.edit',['profile'=>auth()->user()->profile,'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(),'provinces'=>Province::query()->where('is_active',true)->orderBy('name')->get()]); } public function update(UpdateProfileRequest $request): RedirectResponse { $user=$request->user(); $user->update($request->safe()->only(['first_name','last_name','email'])); $user->profile()->updateOrCreate(['user_id'=>$user->id],$request->safe()->only(['business_name','address','province_id','city_id','postal_code'])); return back()->with('success','پروفایل به‌روزرسانی شد.'); } }
