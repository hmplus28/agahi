<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Ads\Enums\AdStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ad extends Model
{
    protected $fillable = ['code','slug','user_id','title','normalized_title','normalized_title_hash','description','normalized_description','normalized_description_hash','price','full_name','business_name','country_id','province_id','city_id','address','mobile_1','show_mobile_1','mobile_2','phone_1','phone_2','email','keywords','category_id','referrer','source','submit_ip','status','published_at','expires_at','sort_at','last_ladder_at','is_featured','is_colored','is_urgent','auto_ladder','deleted_at'];

    protected function casts(): array
    {
        return ['status' => AdStatus::class, 'price' => 'integer', 'keywords' => 'array', 'published_at' => 'datetime', 'expires_at' => 'datetime', 'sort_at' => 'datetime', 'last_ladder_at' => 'datetime', 'deleted_at' => 'datetime', 'show_mobile_1' => 'boolean', 'is_featured' => 'boolean', 'is_colored' => 'boolean', 'is_urgent' => 'boolean', 'auto_ladder' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $ad): void {
            $ad->code ??= strtoupper(Str::random(10));
            $ad->sort_at ??= now();
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function province(): BelongsTo { return $this->belongsTo(Province::class); }
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
    public function images(): HasMany { return $this->hasMany(AdImage::class)->orderBy('sort_order'); }
    public function primaryImage(): HasMany { return $this->hasMany(AdImage::class)->where('is_primary', true); }
    public function statusHistory(): HasMany { return $this->hasMany(AdStatusHistory::class); }
    public function links(): HasMany { return $this->hasMany(AdLink::class)->where('is_active', true)->orderBy('sort_order'); }
    public function allLinks(): HasMany { return $this->hasMany(AdLink::class)->orderBy('sort_order'); }
    public function expiryReminders(): HasMany { return $this->hasMany(AdExpiryReminder::class); }
    public function adServices(): HasMany { return $this->hasMany(AdService::class); }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', AdStatus::Active)->whereNull('deleted_at')->whereNotNull('published_at');
    }

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderByDesc('sort_at')->orderByDesc('published_at');
    }

    public function publicUrl(): string { return route('ads.show', ['ad' => $this->code, 'slug' => $this->slug]); }



    public function displayPrice(): ?int
    {


        if ($this->price === null) {
            return null;
        }

        $apply = (bool) \App\Models\SiteSetting::get('pricing.apply_pricing_to_display', config('agahi.apply_pricing_to_display', true));
        if (!$apply) {
            return (int) $this->price;
        }

        $perLink = (int) \App\Models\SiteSetting::get('pricing.price_per_link', config('agahi.price_per_link', 10));
        $perImage = (int) \App\Models\SiteSetting::get('pricing.price_per_extra_image', config('agahi.price_per_extra_image', 20));


        $linkCount = $this->relationLoaded('links') ? $this->links->count() : $this->links()->count();
        $imageCount = $this->relationLoaded('images') ? $this->images->count() : $this->images()->count();

        $surcharges = ($linkCount * $perLink) + (max(0, $imageCount - 1) * $perImage);

        return (int) $this->price + $surcharges;
    }

    public function priceLabel(): string
    {
        $price = $this->displayPrice();
        return $price === null ? 'توافقی' : number_format($price).' ریال';
    }
}
