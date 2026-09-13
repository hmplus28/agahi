<?php

return [
    'free_ad_duration_days' => (int) env('FREE_AD_DURATION_DAYS', 30),
    'max_images' => (int) env('MAX_AD_IMAGES', 5),
    'max_links' => (int) env('MAX_AD_LINKS', 5),
    'image_max_bytes' => (int) env('IMAGE_MAX_BYTES', 5 * 1024 * 1024),
    'image_max_pixels' => (int) env('IMAGE_MAX_PIXELS', 24_000_000),
    'site_description' => env('SITE_DESCRIPTION', 'سامانهٔ سریع و امن ثبت آگهی در ایران'),

    // Branding & contact info rendered in the site header and footer.
    'brand_name' => env('BRAND_NAME', 'آگهی'),
    'support_phone' => env('SUPPORT_PHONE', '021-00000000'),
    'support_phone_href' => env('SUPPORT_PHONE_HREF', '02100000000'),

    // ─── Pricing multipliers (admin-configurable) ───────────────────────────
    // Each filled link in an ad adds this amount to the ad's effective price.
    // Stored as integer toman (no decimals) to match the rest of the billing
    // pipeline. Default is 10 toman per link.
    'price_per_link' => (int) env('PRICE_PER_LINK', 10),

    // Each image BEYOND the first one adds this amount (so an ad with 3
    // images adds price_per_extra_image × 2). Default is 20 toman.
    'price_per_extra_image' => (int) env('PRICE_PER_EXTRA_IMAGE', 20),

    // Whether the price multipliers above should be applied automatically
    // when computing an ad's display price, or only when paying for the
    // ad tariff itself. When true, the display price on the card and the
    // ad page will already include the link and image surcharges.
    'apply_pricing_to_display' => (bool) env('APPLY_PRICING_TO_DISPLAY', true),
];
