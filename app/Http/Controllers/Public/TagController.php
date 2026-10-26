<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Support\PersianNormalizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Request $request, string $keyword): View
    {


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
