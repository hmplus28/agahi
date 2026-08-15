"""Moderation URLs."""
from django.urls import path

from . import views

app_name = 'moderation'
urlpatterns = [
    path('reports/', views.report_list, name='report_list'),
    path('reports/<int:pk>/', views.update_report, name='update_report'),
]
