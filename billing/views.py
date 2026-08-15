"""Billing views for user-owned advertisement services."""
from django.conf import settings
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import ValidationError
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.views.decorators.http import require_POST

from ads.models import Ad

from .models import Invoice, Payment, Tariff
from .services.orders import create_checkout, finalize_payment, validate_tariff_purchase
from .services.payment_gateway import PaymentGatewayConfigurationError, request_payment, verify_payment


@login_required
def invoice_list(request):
    invoices = Invoice.objects.filter(user=request.user).select_related('ad').prefetch_related('items')
    return render(request, 'billing/invoice_list.html', {'invoices': invoices})


@login_required
def tariff_list(request, ad_id):
    ad = get_object_or_404(Ad, pk=ad_id, user=request.user, deleted_at__isnull=True)
    tariffs = Tariff.objects.filter(is_active=True)
    if ad.status == 'expired':
        tariffs = tariffs.filter(service_type__in=['annual_ad', 'annual_renewal'])
    elif ad.status != 'active':
        tariffs = tariffs.none()
        messages.warning(request, 'در وضعیت فعلی خرید خدمت برای این آگهی مجاز نیست.')
    return render(request, 'billing/tariff_list.html', {'ad': ad, 'tariffs': tariffs.order_by('sort_order', 'title')})


@login_required
def checkout(request, tariff_id, ad_id):
    tariff = get_object_or_404(Tariff, pk=tariff_id, is_active=True)
    ad = get_object_or_404(Ad, pk=ad_id, user=request.user, deleted_at__isnull=True)
    try:
        validate_tariff_purchase(tariff=tariff, ad=ad)
    except ValidationError as exc:
        messages.error(request, exc.messages[0])
        return redirect('billing:tariff_list', ad_id=ad.pk)
    if request.method == 'GET':
        return render(request, 'billing/checkout.html', {'tariff': tariff, 'ad': ad})
    try:
        order, invoice, payment = create_checkout(user=request.user, tariff=tariff, ad=ad)
    except ValidationError as exc:
        messages.error(request, exc.messages[0])
        return redirect('billing:tariff_list', ad_id=ad.pk)
    if payment.status == 'successful':
        messages.success(request, 'خدمت رایگان با موفقیت اعمال شد.')
        return redirect('billing:invoice_list')
    callback_url = request.build_absolute_uri(reverse('billing:payment_callback'))
    payment.gateway = settings.DEFAULT_PAYMENT_GATEWAY
    payment.save(update_fields=['gateway'])
    try:
        gateway_response = request_payment(
            int(payment.amount), callback_url, f'پرداخت {tariff.title} برای آگهی {ad.code}', mobile=request.user.mobile
        )
    except PaymentGatewayConfigurationError as exc:
        payment.status = 'manual_review'
        payment.admin_note = str(exc)
        payment.save(update_fields=['status', 'admin_note'])
        messages.error(request, 'درگاه پرداخت هنوز پیکربندی نشده است. این سفارش برای پیگیری ثبت شد.')
        return redirect('billing:invoice_list')
    if not gateway_response.success:
        payment.status = 'failed'
        payment.admin_note = gateway_response.error_message or 'خطا در آغاز پرداخت'
        payment.save(update_fields=['status', 'admin_note'])
        messages.error(request, 'ارتباط با درگاه پرداخت برقرار نشد. بعداً دوباره تلاش کنید.')
        return redirect('billing:invoice_list')
    payment.authority = gateway_response.authority
    payment.save(update_fields=['authority'])
    return redirect(gateway_response.redirect_url)


def payment_callback(request):
    authority = request.GET.get('Authority') or request.GET.get('authority')
    status = request.GET.get('Status') or request.GET.get('status')
    payment = get_object_or_404(Payment.objects.select_related('invoice', 'ad'), authority=authority)
    if payment.status == 'successful':
        messages.info(request, 'این پرداخت پیش‌تر با موفقیت تأیید شده است.')
        return redirect('billing:invoice_list')
    if payment.status != 'pending':
        messages.error(request, 'این پرداخت در وضعیت قابل‌تأیید نیست.')
        return redirect('billing:invoice_list')
    if status and status.lower() not in {'ok', 'successful'}:
        payment.status = 'cancelled'
        payment.save(update_fields=['status'])
        messages.error(request, 'پرداخت لغو شد یا توسط درگاه تأیید نشد.')
        return redirect('billing:invoice_list')
    try:
        verification = verify_payment(payment.authority, int(payment.amount), provider=payment.gateway)
    except PaymentGatewayConfigurationError as exc:
        payment.status = 'manual_review'
        payment.admin_note = str(exc)
        payment.save(update_fields=['status', 'admin_note'])
        messages.error(request, 'تأیید خودکار پرداخت در دسترس نیست و سفارش برای پیگیری ثبت شد.')
        return redirect('billing:invoice_list')
    if not verification.success:
        payment.status = 'failed'
        payment.admin_note = verification.error_message or 'تأیید پرداخت ناموفق بود.'
        payment.save(update_fields=['status', 'admin_note'])
        messages.error(request, 'تأیید پرداخت ناموفق بود.')
        return redirect('billing:invoice_list')
    finalize_payment(payment, reference_id=verification.ref_id or '')
    messages.success(request, 'پرداخت با موفقیت تأیید و خدمت آگهی اعمال شد.')
    return redirect('billing:invoice_list')
