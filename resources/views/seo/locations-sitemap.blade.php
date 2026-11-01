@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($cities as $city)
<url><loc>{{ $city['loc'] }}</loc><lastmod>{{ $city['lastmod'] }}</lastmod></url>
@endforeach
@foreach($landings as $landing)
<url><loc>{{ $landing['loc'] }}</loc><lastmod>{{ $landing['lastmod'] }}</lastmod></url>
@endforeach
</urlset>
