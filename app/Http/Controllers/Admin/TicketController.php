<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class TicketController extends Controller { public function index(): View { return view('admin.tickets.index',['tickets'=>Ticket::query()->with('user')->latest()->paginate(50)]); } public function show(Ticket $ticket): View { return view('admin.tickets.show',['ticket'=>$ticket->load(['user','messages.sender'])]); } public function reply(Request $request,Ticket $ticket): RedirectResponse { $data=$request->validate(['message'=>['required','string','max:5000'],'status'=>['required','in:open,waiting_user,waiting_support,closed']]); TicketMessage::query()->create(['ticket_id'=>$ticket->id,'sender_id'=>$request->user()->id,'message'=>$data['message'],'created_at'=>now()]); $ticket->update(['status'=>$data['status'],'closed_at'=>$data['status']==='closed'?now():null]); return back()->with('success','پاسخ ثبت شد.'); } }
