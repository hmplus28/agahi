<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Models\AdPermit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PermitController extends Controller { public function index(): View { return view('admin.permits.index',['permits'=>AdPermit::query()->with('ad')->latest()->paginate(50)]); } public function update(Request $request,AdPermit $permit,AdWorkflow $workflow): RedirectResponse { $data=$request->validate(['status'=>['required','in:pending,approved,rejected'],'admin_note'=>['nullable','string','max:1500']]); $permit->update($data); if($data['status']==='approved' && $permit->ad->status===AdStatus::NeedsPermit) $workflow->transition($permit->ad,AdStatus::Active,$request->user(),'مجوز تأیید شد'); return back()->with('success','وضعیت مجوز به‌روزرسانی شد.'); } }
