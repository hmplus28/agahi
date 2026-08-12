"""
Dashboard URLs.
"""
from django.urls import path
from . import views

app_name = 'dashboard'

urlpatterns = [
    path('', views.index, name='index'),
    path('ads/', views.my_ads, name='my_ads'),
    path('profile/', views.profile, name='profile'),
]
