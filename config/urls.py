"""
URL configuration for config project.

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/5.2/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  path('', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  path('', Home.as_view(), name='home')
Including another URLconf
    1. Import the include() function: from django.urls import include, path
    2. Add a URL to urlpatterns:  path('blog/', include('blog.urls'))
"""
from django.contrib import admin
from django.urls import path, include
from django.conf import settings
from django.conf.urls.static import static
from django.views.generic import TemplateView

urlpatterns = [
    path('admin/', admin.site.urls),
    
    # Apps
    path('', include('core.urls')),
    path('accounts/', include('accounts.urls', namespace='accounts')),
    path('ads/', include('ads.urls', namespace='ads')),
    path('categories/', include('taxonomy.urls', namespace='taxonomy')),
    path('locations/', include('locations.urls', namespace='locations')),
    path('billing/', include('billing.urls', namespace='billing')),
    path('support/', include('support.urls', namespace='support')),
    path('notifications/', include('notifications.urls', namespace='notifications')),
    path('dashboard/', include('dashboard.urls', namespace='dashboard')),
    
    # SEO
    path('robots.txt', TemplateView.as_view(template_name='seo/robots.txt', content_type='text/plain')),
    path('sitemap.xml', TemplateView.as_view(template_name='seo/sitemap.xml', content_type='application/xml')),
]

# Serve media files in development
if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)

# Custom error pages
handler404 = 'core.views.error_404'
handler500 = 'core.views.error_500'

