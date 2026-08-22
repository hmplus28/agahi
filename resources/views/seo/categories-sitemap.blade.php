<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($categories as $category)
<url><loc>{{ route('categories.show',['category'=>$category['slug']]) }}</loc><lastmod>{{ $category['lastmod'] }}</lastmod></url>
@endforeach
</urlset>
