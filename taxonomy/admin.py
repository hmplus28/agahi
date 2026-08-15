"""Django Admin for hierarchical categories."""
from django.contrib import admin

from .models import Category


@admin.register(Category)
class CategoryAdmin(admin.ModelAdmin):
    list_display = ('title', 'parent', 'slug', 'sort_order', 'is_active', 'updated_at')
    list_filter = ('is_active', 'parent')
    search_fields = ('title', 'slug', 'description', 'seo_title')
    list_select_related = ('parent',)
    prepopulated_fields = {'slug': ('title',)}
    ordering = ('sort_order', 'title')
    readonly_fields = ('created_at', 'updated_at')
