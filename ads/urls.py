"""
Ads app URLs.
"""
from django.urls import path
from . import views

app_name = 'ads'

urlpatterns = [
    path('', views.ad_list, name='ad_list'),
    path('create/', views.ad_create, name='ad_create'),
    path('<int:pk>/', views.ad_detail, name='ad_detail'),
    path('<int:pk>/edit/', views.ad_edit, name='ad_edit'),
    path('<int:pk>/delete/', views.ad_delete, name='ad_delete'),
]
