"""Staff moderation views for advertisement reports."""
from django.contrib import messages
from django.contrib.admin.views.decorators import staff_member_required
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone
from django.views.decorators.http import require_POST

from ads.models import AdReport


@staff_member_required
def report_list(request):
    reports = AdReport.objects.select_related('ad', 'reporter_user').order_by('status', '-created_at')
    return render(request, 'moderation/report_list.html', {'reports': reports})


@staff_member_required
@require_POST
def update_report(request, pk):
    report = get_object_or_404(AdReport, pk=pk)
    status = request.POST.get('status')
    if status not in {'reviewing', 'resolved', 'rejected'}:
        messages.error(request, 'وضعیت انتخاب‌شده معتبر نیست.')
    else:
        report.status = status
        report.admin_note = request.POST.get('admin_note', '').strip()
        report.reviewed_at = timezone.now() if status in {'resolved', 'rejected'} else None
        report.save(update_fields=['status', 'admin_note', 'reviewed_at'])
        messages.success(request, 'گزارش به‌روزرسانی شد.')
    return redirect('moderation:report_list')
