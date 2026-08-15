"""Small public endpoints for dependent location form fields."""
from django.http import JsonResponse
from django.views.decorators.http import require_GET

from .models import City, Province


@require_GET
def province_list(request):
    country_id = request.GET.get('country')
    provinces = Province.objects.filter(is_active=True)
    if country_id:
        provinces = provinces.filter(country_id=country_id)
    data = list(provinces.values('id', 'name', 'slug').order_by('sort_order', 'name'))
    return JsonResponse({'results': data})


@require_GET
def city_list(request):
    province_id = request.GET.get('province')
    cities = City.objects.filter(is_active=True)
    if province_id:
        cities = cities.filter(province_id=province_id)
    data = list(cities.values('id', 'name', 'slug').order_by('sort_order', 'name'))
    return JsonResponse({'results': data})
