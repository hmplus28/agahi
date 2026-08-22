<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ReportController extends Controller { public function index(): View { return view('admin.reports.index',['reports'=>AdReport::query()->with(['ad','ad.user'])->latest()->paginate(50)]); } public function update(Request $request,AdReport $report): RedirectResponse { $data=$request->validate(['status'=>['required','in:new,reviewing,resolved,rejected'],'admin_note'=>['nullable','string','max:1500']]); $report->update([...$data,'reviewed_at'=>in_array($data['status'],['resolved','rejected'],true)?now():null]); return back()->with('success','گزارش به‌روزرسانی شد.'); } }
