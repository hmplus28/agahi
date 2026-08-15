"""Transactional checkout and tariff-application services."""
from datetime import timedelta

from django.core.exceptions import PermissionDenied, ValidationError
from django.db import transaction
from django.utils import timezone

from ads.models import AdStatus
from billing.models import AdService, Invoice, InvoiceItem, Order, OrderItem, Payment, Tariff


def create_checkout(*, user, tariff, ad):
    """Create immutable accounting records for one tariff purchase."""
    if ad.user_id != user.id:
        raise PermissionDenied('شما مجاز به خرید خدمت برای این آگهی نیستید.')
    if not tariff.is_active:
        raise ValidationError('این تعرفه فعال نیست.')
    with transaction.atomic():
        amount = tariff.price
        order = Order.objects.create(user=user, total_amount=amount)
        OrderItem.objects.create(
            order=order,
            tariff=tariff,
            quantity=1,
            unit_price=amount,
            total_price=amount,
            metadata={'ad_id': ad.id},
        )
        invoice = Invoice.objects.create(user=user, ad=ad, subtotal=amount, total=amount)
        InvoiceItem.objects.create(
            invoice=invoice,
            title=tariff.title,
            quantity=1,
            unit_price=amount,
            total_price=amount,
            metadata={'tariff_id': tariff.id, 'order_id': order.id},
        )
        payment = Payment.objects.create(
            user=user,
            ad=ad,
            invoice=invoice,
            amount=amount,
            method='free' if amount == 0 else 'online',
            status='pending',
        )
        if amount == 0:
            finalize_payment(payment, reference_id='free-service')
    return order, invoice, payment


def _apply_service(*, ad, tariff):
    now = timezone.now()
    expires_at = now + timedelta(days=tariff.duration_days) if tariff.duration_days else None
    service = AdService.objects.create(ad=ad, tariff=tariff, starts_at=now, expires_at=expires_at)
    update_fields = []
    if tariff.service_type in {'annual_ad', 'annual_renewal'}:
        base = ad.expires_at if ad.expires_at and ad.expires_at > now else now
        ad.expires_at = base + timedelta(days=tariff.duration_days)
        update_fields.append('expires_at')
        if ad.status == AdStatus.EXPIRED:
            ad.status = AdStatus.PENDING_APPROVAL
            update_fields.append('status')
    elif tariff.service_type == 'featured':
        ad.is_featured = True
        update_fields.append('is_featured')
    elif tariff.service_type == 'colored_card':
        ad.is_colored = True
        update_fields.append('is_colored')
    elif tariff.service_type == 'urgent':
        ad.is_urgent = True
        update_fields.append('is_urgent')
    elif tariff.service_type == 'ladder':
        ad.sort_at = now
        ad.last_ladder_at = now
        update_fields.extend(['sort_at', 'last_ladder_at'])
    elif tariff.service_type == 'auto_ladder':
        ad.auto_ladder = True
        update_fields.append('auto_ladder')
    if update_fields:
        update_fields.append('updated_at')
        ad.save(update_fields=update_fields)
    return service


def finalize_payment(payment, *, reference_id=''):
    """Idempotently mark a verified payment successful and grant its service."""
    with transaction.atomic():
        payment = Payment.objects.select_for_update().select_related('invoice', 'ad').get(pk=payment.pk)
        if payment.status == 'successful':
            return payment
        order_item = (
            OrderItem.objects.select_related('order', 'tariff')
            .filter(metadata__ad_id=payment.ad_id, order__user_id=payment.user_id, order__status='pending')
            .order_by('-order__created_at')
            .first()
        )
        if not order_item:
            raise ValidationError('سفارش متناظر با پرداخت پیدا نشد.')
        now = timezone.now()
        payment.status = 'successful'
        payment.reference_id = reference_id
        payment.paid_at = now
        payment.verified_at = now
        payment.save(update_fields=['status', 'reference_id', 'paid_at', 'verified_at'])
        invoice = payment.invoice
        invoice.status = 'paid'
        invoice.paid_at = now
        invoice.save(update_fields=['status', 'paid_at'])
        order_item.order.status = 'completed'
        order_item.order.save(update_fields=['status', 'updated_at'])
        _apply_service(ad=payment.ad, tariff=order_item.tariff)
    return payment
