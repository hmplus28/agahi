<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Domains\Ads\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Ticket;
use Illuminate\View\View;
class DashboardController extends Controller { public function __invoke(): View { $counts=[]; foreach (AdStatus::cases() as $status) $counts[$status->value]=Ad::query()->where('status',$status)->count(); return view('admin.dashboard',['counts'=>$counts,'openTickets'=>Ticket::query()->whereIn('status',['open','waiting_support'])->count()]); } }
