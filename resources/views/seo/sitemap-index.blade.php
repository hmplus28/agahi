@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($staticPages ?? [] as $page)
<sitemap><loc>{{ $page['loc'] }}</loc><lastmod>{{ $page['lastmod'] }}</lastmod></sitemap>
@endforeach
@foreach($pages as $page)
<sitemap><loc>{{ route('sitemap.ads', ['page' => $page]) }}</loc></sitemap>
@endforeach
<sitemap><loc>{{ route('sitemap.categories') }}</loc></sitemap>
</sitemapindex>
