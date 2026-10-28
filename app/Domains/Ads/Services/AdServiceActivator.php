<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\AdService;
use App\Models\Tariff;
use DateTimeInterface;

/**
 * Single source of truth for attaching paid/free services (tariffs) to ads
 * and applying their visual/behavioural effects (featured, urgent, colored,
 * ladder, expiry extension).
 *
 * Used by:
 *  - guest ad submission (free options applied instantly, paid ones parked
 *    as "pending" until an admin activates them after payment coordination)
 *  - user panel submission (free instantly, paid ones go through an invoice
 *    and are applied by PaymentService on settlement)
 *  - admin moderation (manual activation of pending services)
 */
final class AdServiceActivator
{
    public function __construct(private readonly AdWorkflow $workflow) {}

    /**
     * Create the ad_services row for the given tariff and, when the row is
     * created as active, immediately apply the tariff's effects on the ad.
     */
    public function attach(Ad $ad, Tariff $tariff, string $status = 'active', array $metadata = []): AdService
    {
        $service = AdService::query()->create([
            'ad_id'      => $ad->id,
            'tariff_id'  => $tariff->id,
            'starts_at'  => now(),
            'expires_at' => $tariff->duration_days ? now()->addDays($tariff->duration_days) : null,
            'status'     => $status,
            'metadata'   => $metadata ?: null,
        ]);

        if ($status === 'active') {
            $this->apply($ad, $tariff);
        }

        return $service;
    }

    /**
     * Flip a pending (unpaid / not yet confirmed) service row to active and
     * apply its effects. Safe to call repeatedly.
     */
    public function activate(AdService $service): void
    {
        if ($service->status === 'active') {
            return;
        }

        $service->update([
            'status'    => 'active',
            'starts_at' => $service->starts_at ?? now(),
        ]);

        $tariff = $service->tariff()->first();
        $ad = $service->ad()->first();

        if ($tariff && $ad) {
            $this->apply($ad, $tariff);
        }
    }

    /**
     * Apply the effects of a tariff on the ad without touching ad_services.
     */
    public function apply(Ad $ad, Tariff $tariff): void
    {
        $expires = $tariff->duration_days ? now()->addDays($tariff->duration_days) : null;

        match ($tariff->service_type) {
            'renewal', 'ad' => $this->extend($ad, $expires),
            'featured'      => $ad->update(['is_featured' => true]),
            'urgent'        => $ad->update(['is_urgent' => true]),
            'colored'       => $ad->update(['is_colored' => true]),
            'ladder'        => $ad->update(['last_ladder_at' => now(), 'sort_at' => now()]),
            default         => null, // links / more_images / seo_keywords / promotion / domain
        };
    }

    private function extend(Ad $ad, ?DateTimeInterface $expires): void
    {
        if ($expires) {
            $ad->update(['expires_at' => $expires]);
        }

        if ($ad->status === AdStatus::Expired) {
            $this->workflow->transition($ad, AdStatus::Active, null, 'تمدید پس از فعال‌سازی خدمات');
        }
    }
}
