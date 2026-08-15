"""Location endpoints."""
from django.urls import path

from . import views

app_name = 'locations'
urlpatterns = [
    path('provinces/', views.province_list, name='province_list'),
    path('cities/', views.city_list, name='city_list'),
]
