"""
Core app URLs - Home page and error pages.
"""
from django.urls import path
from . import views

app_name = 'core'

urlpatterns = [
    path('', views.home, name='home'),
]
