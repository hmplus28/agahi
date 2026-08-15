"""Project URL configuration."""
from django.conf import settings
from django.conf.urls.static import static
from django.contrib import admin
from django.urls import include, path

from seo import views as seo_views

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', include('core.urls')),
    path('accounts/', include('accounts.urls', namespace='accounts')),
    path('ads/', include('ads.urls', namespace='ads')),
    path('categories/', include('taxonomy.urls', namespace='taxonomy')),
    path('locations/', include('locations.urls', namespace='locations')),
    path('billing/', include('billing.urls', namespace='billing')),
    path('support/', include('support.urls', namespace='support')),
    path('notifications/', include('notifications.urls', namespace='notifications')),
    path('moderation/', include('moderation.urls', namespace='moderation')),
    path('dashboard/', include('dashboard.urls', namespace='dashboard')),
    path('robots.txt', seo_views.robots_txt, name='robots_txt'),
    path('sitemap.xml', seo_views.sitemap_xml, name='sitemap_xml'),
]

if settings.DEBUG:
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)

handler404 = 'core.views.error_404'
handler500 = 'core.views.error_500'
