<?php

return [
    'free_ad_duration_days' => (int) env('FREE_AD_DURATION_DAYS', 30),
    'max_images' => (int) env('MAX_AD_IMAGES', 5),
    'max_links' => (int) env('MAX_AD_LINKS', 5),
    'image_max_bytes' => (int) env('IMAGE_MAX_BYTES', 5 * 1024 * 1024),
    'image_max_pixels' => (int) env('IMAGE_MAX_PIXELS', 24_000_000),
    'site_description' => env('SITE_DESCRIPTION', 'سامانهٔ سریع و امن ثبت آگهی در ایران'),


    'brand_name' => env('BRAND_NAME', 'آگهی'),
    'support_phone' => env('SUPPORT_PHONE', '021-00000000'),
    'support_phone_href' => env('SUPPORT_PHONE_HREF', '02100000000'),





    'price_per_link' => (int) env('PRICE_PER_LINK', 10),



    'price_per_extra_image' => (int) env('PRICE_PER_EXTRA_IMAGE', 20),





    'apply_pricing_to_display' => (bool) env('APPLY_PRICING_TO_DISPLAY', true),
];
