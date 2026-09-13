@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($ads as $ad)
@php
    // For ads with an active ladder service, use the most recent of
    // (last_ladder_at, sort_at, updated_at) as the lastmod — that way
    // Google sees a fresh timestamp every time the cron bumps ladders.
    $lastmod = $ad->updated_at;
    if ($ad->last_ladder_at && $ad->last_ladder_at->gt($lastmod)) {
        $lastmod = $ad->last_ladder_at;
    }
    if ($ad->sort_at && $ad->sort_at->gt($lastmod)) {
        $lastmod = $ad->sort_at;
    }
@endphp
<url><loc>{{ $seo->canonicalForAd($ad) }}</loc><lastmod>{{ $lastmod->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
