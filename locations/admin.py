"""Django Admin for location hierarchy."""
from django.contrib import admin

from .models import City, Country, Province


@admin.register(Country)
class CountryAdmin(admin.ModelAdmin):
    list_display = ('name', 'slug', 'sort_order', 'is_active')
    list_filter = ('is_active',)
    search_fields = ('name', 'slug')
    prepopulated_fields = {'slug': ('name',)}
    ordering = ('sort_order', 'name')


@admin.register(Province)
class ProvinceAdmin(admin.ModelAdmin):
    list_display = ('name', 'country', 'slug', 'sort_order', 'is_active')
    list_filter = ('is_active', 'country')
    search_fields = ('name', 'slug', 'country__name')
    list_select_related = ('country',)
    prepopulated_fields = {'slug': ('name',)}
    ordering = ('country', 'sort_order', 'name')


@admin.register(City)
class CityAdmin(admin.ModelAdmin):
    list_display = ('name', 'province', 'country_name', 'slug', 'sort_order', 'is_active')
    list_filter = ('is_active', 'province__country', 'province')
    search_fields = ('name', 'slug', 'province__name', 'province__country__name')
    list_select_related = ('province', 'province__country')
    prepopulated_fields = {'slug': ('name',)}
    ordering = ('province__country', 'province', 'sort_order', 'name')

    @admin.display(description='کشور', ordering='province__country__name')
    def country_name(self, obj):
        return obj.province.country.name
