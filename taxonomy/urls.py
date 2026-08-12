"""
Taxonomy app URLs.
"""
from django.urls import path
from . import views

app_name = 'taxonomy'

urlpatterns = [
    # Category listing
    path('', views.CategoryListView.as_view(), name='category_list'),
    # Category detail with ads
    path('<int:pk>/', views.CategoryDetailView.as_view(), name='category_detail'),
    path('<int:pk>/<slug:slug>/', views.CategoryDetailView.as_view(), name='category_detail_slug'),
]
