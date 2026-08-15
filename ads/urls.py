"""Advertisement URLs."""
from django.urls import path

from . import views

app_name = 'ads'

urlpatterns = [
    path('', views.ad_list, name='ad_list'),
    path('create/', views.ad_create, name='ad_create'),
    path('<int:pk>/', views.ad_detail, name='ad_detail'),
    path('<int:pk>/edit/', views.ad_edit, name='ad_edit'),
    path('<int:pk>/report/', views.ad_report_create, name='ad_report_create'),
    path('<int:pk>/permit/', views.ad_permit_upload, name='ad_permit_upload'),
    path('<int:pk>/links/', views.ad_links, name='ad_links'),
    path('<int:pk>/links/<int:link_id>/delete/', views.ad_link_delete, name='ad_link_delete'),
    path('<int:pk>/delete/', views.ad_delete, name='ad_delete'),
]
