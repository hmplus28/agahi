<?php
declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class TicketController extends Controller {
    public function index(): View {  return view('user.tickets.index',['tickets'=>Ticket::query()->where('user_id',auth()->user()->id)->latest()->paginate(20)]); }

    /** PRD §29 - user sees the full ticket conversation. */
    public function show(Ticket $ticket): View
    {
        abort_unless($ticket->user_id === auth()->user()->id, 404);
        $ticket->load(['messages.sender']);

        return view('user.tickets.show', ['ticket' => $ticket]);
    }

    /** PRD §29 - user reply; replying to a closed ticket reopens it. */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === auth()->user()->id, 404);

        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        TicketMessage::query()->create([
            'ticket_id'  => $ticket->id,
            'sender_id'  => $request->user()->id,
            'message'    => $data['message'],
            'created_at' => now(),
        ]);

        $wasClosed = $ticket->status === 'closed';
        $ticket->update([
            'status'    => 'waiting_support',
            'closed_at' => $wasClosed ? null : $ticket->closed_at,
        ]);

        return back()->with('success', $wasClosed ? 'پاسخ شما ثبت شد و تیکت دوباره باز شد.' : 'پاسخ شما ثبت شد.');
    }

    public function store(StoreTicketRequest $request): RedirectResponse { $ticket=Ticket::query()->create(['user_id'=>$request->user()->id,'subject'=>$request->input('subject'),'priority'=>$request->input('priority','normal')]); TicketMessage::query()->create(['ticket_id'=>$ticket->id,'sender_id'=>$request->user()->id,'message'=>$request->input('message'),'created_at'=>now()]); return back()->with('success','تیکت شما ثبت شد.'); }
}
