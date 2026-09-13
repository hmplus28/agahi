<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Support\PersianNormalizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Keyword tag page.
 *
 * When a user clicks on a keyword shown on an ad page, we take them here
 * — /tag/{keyword} — which lists all publicly visible ads that have that
 * keyword in their `keywords` JSON column.
 */
class TagController extends Controller
{
    public function show(Request $request, string $keyword): View
    {
        // Decode URL-encoded Persian text and normalize so that searches
        // match regardless of ZWNJ / Arabic-vs-Persian letter differences.
        $keyword = PersianNormalizer::text(rawurldecode($keyword));

        $ads = Ad::query()
            ->publiclyVisible()
            ->with(['city', 'images', 'category'])
            ->whereJsonContains('keywords', $keyword)
            ->orderedForListing()
            ->paginate(24)
            ->withQueryString();

        return view('public.tag', [
            'keyword' => $keyword,
            'ads'     => $ads,
        ]);
    }
}
