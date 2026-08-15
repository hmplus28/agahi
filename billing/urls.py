"""Billing URLs."""
from django.urls import path

from . import views

app_name = 'billing'
urlpatterns = [
    path('', views.invoice_list, name='invoice_list'),
    path('tariffs/<int:ad_id>/', views.tariff_list, name='tariff_list'),
    path('checkout/<int:tariff_id>/<int:ad_id>/', views.checkout, name='checkout'),
    path('callback/', views.payment_callback, name='payment_callback'),
]
