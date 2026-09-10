<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tariff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TariffCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['code' => 'FREE_30', 'title' => 'رایگان یک ماهه', 'description' => 'نمایش آگهی به مدت ۳۰ روز به‌صورت رایگان', 'price' => 0, 'service_type' => 'ad', 'duration_days' => 30, 'sort_order' => 1],
            ['code' => 'YEARLY', 'title' => 'یکساله', 'description' => 'نمایش آگهی به مدت یک سال', 'price' => 2000, 'service_type' => 'ad', 'duration_days' => 365, 'sort_order' => 2],
            ['code' => 'FEATURED', 'title' => 'ویژه (همیشه بالای آگهی‌ها)', 'description' => 'نمایش آگهی در موقعیت ویژه و بالای نتایج', 'price' => 100, 'service_type' => 'featured', 'duration_days' => 30, 'sort_order' => 3],
            ['code' => 'COLORED', 'title' => 'رنگی (رنگ زمینه زرد)', 'description' => 'نمایش آگهی با پس‌زمینه زرد در فهرست‌ها', 'price' => 100, 'service_type' => 'colored', 'duration_days' => 30, 'sort_order' => 4],
            ['code' => 'URGENT', 'title' => 'فوری (درج عبارت فوری)', 'description' => 'نمایش آگهی با نشان فوری و اولویت بیشتر', 'price' => 100, 'service_type' => 'urgent', 'duration_days' => 30, 'sort_order' => 5],
            ['code' => 'LINKS', 'title' => 'لینک‌ها (هر عدد)', 'description' => 'نمایش لینک‌های سایت و شبکه‌های اجتماعی در آگهی', 'price' => 100, 'service_type' => 'links', 'duration_days' => 30, 'sort_order' => 6],
            ['code' => 'LADDER_365', 'title' => 'نردبان اتوماتیک ۳۶۵ روزه (روزانه یکبار)', 'description' => 'بالا آمدن خودکار آگهی در فهرست به مدت یک سال', 'price' => 100, 'service_type' => 'ladder', 'duration_days' => 365, 'sort_order' => 7],
            ['code' => 'AD_SITES', 'title' => 'تبلیغ آگهی در سایت‌های دیگر (۵۰ عدد)', 'description' => 'نمایش آگهی در سایت‌های شریک تبلیغاتی', 'price' => 100, 'service_type' => 'promotion', 'duration_days' => 30, 'sort_order' => 8],
            ['code' => 'DOMAIN_CONNECT', 'title' => 'اتصال دامین IR به آگهی (سایت ساده)', 'description' => 'اتصال دامنه شخصی شما به آگهی', 'price' => 100, 'service_type' => 'domain', 'duration_days' => 30, 'sort_order' => 9],
            ['code' => 'MORE_IMAGES', 'title' => 'تصاویر (بیشتر از یک عدد)', 'description' => 'افزایش سقف تصاویر قابل بارگذاری برای آگهی', 'price' => 100, 'service_type' => 'more_images', 'duration_days' => 30, 'sort_order' => 10],
            ['code' => 'SEO_KEYWORDS', 'title' => 'تقویت لغات کلیدی (سئو)', 'description' => 'ارتقای جایگاه آگهی در جستجوی لغات کلیدی', 'price' => 100, 'service_type' => 'seo_keywords', 'duration_days' => 30, 'sort_order' => 11],
        ];

        foreach ($catalog as $item) {
            Tariff::query()->updateOrCreate(
                ['code' => $item['code']],
                [...$item, 'is_active' => true, 'settings' => ['slug' => Str::slug($item['title'])], 'sort_order' => $item['sort_order']],
            );
        }
    }
}