<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class TicketController extends Controller { public function index(): View { return view('user.tickets.index',['tickets'=>Ticket::query()->where('user_id',auth()->id())->latest()->paginate(20)]); } public function store(StoreTicketRequest $request): RedirectResponse { $ticket=Ticket::query()->create(['user_id'=>$request->user()->id,'subject'=>$request->input('subject'),'priority'=>$request->input('priority','normal')]); TicketMessage::query()->create(['ticket_id'=>$ticket->id,'sender_id'=>$request->user()->id,'message'=>$request->input('message'),'created_at'=>now()]); return back()->with('success','تیکت شما ثبت شد.'); } }
