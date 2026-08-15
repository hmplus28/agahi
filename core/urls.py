"""
Core app URLs - Home page and error pages.
"""
from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('', views.home, name='home'),
    path('service-worker.js', views.service_worker, name='service_worker'),
    path('offline/', views.offline_page, name='offline_page'),
    path('healthz/', views.healthcheck, name='healthcheck'),
]
