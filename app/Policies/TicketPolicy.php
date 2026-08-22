<?php

declare(strict_types=1);
namespace App\Policies;
use App\Models\Ticket;
use App\Models\User;
class TicketPolicy { public function view(User $user, Ticket $ticket): bool { return $ticket->user_id === $user->id || $user->is_staff; } public function reply(User $user, Ticket $ticket): bool { return $ticket->user_id === $user->id || $user->is_staff; } }
