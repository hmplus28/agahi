"""Dashboard URLs."""
from django.urls import path

from . import staff_views, views

app_name = 'dashboard'

urlpatterns = [
    path('', views.index, name='index'),
    path('ads/', views.my_ads, name='my_ads'),
    path('profile/', views.profile, name='profile'),
    path('management/', staff_views.staff_overview, name='staff_overview'),
]
