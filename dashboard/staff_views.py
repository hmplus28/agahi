"""Staff-only operational overview for the classified-ads platform."""
from django.contrib.auth.decorators import permission_required
from django.db.models import Count, Q
from django.shortcuts import render
from django.utils import timezone

from ads.models import Ad, AdReport, AdStatus
from billing.models import Payment
from support.models import Ticket


@permission_required('ads.view_ad', raise_exception=True)
def staff_overview(request):
    """Render cached-by-request aggregate operational counts without N+1 queries."""
    ad_stats = Ad.objects.aggregate(
        total=Count('id'),
        pending=Count('id', filter=Q(status=AdStatus.PENDING_APPROVAL)),
        active=Count('id', filter=Q(status=AdStatus.ACTIVE)),
        expired=Count('id', filter=Q(status=AdStatus.EXPIRED)),
        needs_permit=Count('id', filter=Q(status=AdStatus.NEEDS_PERMIT)),
        inactive=Count('id', filter=Q(status=AdStatus.INACTIVE)),
        deleted=Count('id', filter=Q(status=AdStatus.DELETED)),
    )
    today = timezone.localdate()
    context = {
        'ad_stats': ad_stats,
        'payments_today': Payment.objects.filter(status='successful', paid_at__date=today).count(),
        'open_tickets': Ticket.objects.exclude(status='closed').count(),
        'new_reports': AdReport.objects.filter(status='new').count(),
    }
    return render(request, 'dashboard/staff_overview.html', context)
