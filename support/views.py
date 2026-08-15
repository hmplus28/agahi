"""Support ticket views."""
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.db import transaction
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone

from core.rate_limit import rate_limit

from .forms import TicketCreateForm, TicketMessageForm
from .models import Ticket, TicketMessage


@login_required
def ticket_list(request):
    tickets = request.user.tickets.order_by('-updated_at')
    return render(request, 'support/ticket_list.html', {'tickets': tickets})


@login_required
@rate_limit('ticket-create', limit=8, period=3600)
def ticket_create(request):
    form = TicketCreateForm(request.POST or None)
    if request.method == 'POST' and form.is_valid():
        with transaction.atomic():
            ticket = form.save(commit=False)
            ticket.user = request.user
            ticket.save()
            TicketMessage.objects.create(ticket=ticket, sender=request.user, message=form.cleaned_data['message'])
        messages.success(request, 'تیکت شما ثبت شد.')
        return redirect('support:ticket_detail', pk=ticket.pk)
    return render(request, 'support/ticket_form.html', {'form': form})


@login_required
@rate_limit('ticket-reply', limit=20, period=3600)
def ticket_detail(request, pk):
    ticket = get_object_or_404(Ticket.objects.prefetch_related('messages__sender'), pk=pk)
    if ticket.user_id != request.user.id and not request.user.is_staff:
        return redirect('support:ticket_list')
    form = TicketMessageForm(request.POST or None)
    if request.method == 'POST' and form.is_valid():
        if ticket.status == 'closed':
            messages.error(request, 'تیکت بسته شده و امکان پاسخ جدید ندارد.')
        else:
            TicketMessage.objects.create(ticket=ticket, sender=request.user, message=form.cleaned_data['message'])
            ticket.status = 'waiting_user' if request.user.is_staff else 'waiting_support'
            ticket.save(update_fields=['status', 'updated_at'])
            messages.success(request, 'پاسخ شما ثبت شد.')
            return redirect('support:ticket_detail', pk=ticket.pk)
    return render(request, 'support/ticket_detail.html', {'ticket': ticket, 'form': form})
