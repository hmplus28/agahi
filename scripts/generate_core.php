<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'app/Domains/Ads/Enums/AdStatus.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Ads\Enums;

enum AdStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case NeedsPermit = 'needs_permit';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::PendingApproval => 'در انتظار تأیید',
            self::Active => 'فعال',
            self::NeedsPermit => 'نیازمند مجوز',
            self::Inactive => 'غیرفعال',
            self::Expired => 'منقضی',
            self::Deleted => 'حذف‌شده',
        };
    }
}
PHP,
'app/Domains/Accounts/Enums/UserRole.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Accounts\Enums;

enum UserRole: string
{
    case User = 'user';
    case SuperAdmin = 'super_admin';
    case Moderator = 'moderator';
    case Support = 'support';
    case Accounting = 'accounting';

    public function isStaff(): bool
    {
        return $this !== self::User;
    }
}
PHP,
'app/Models/User.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Accounts\Enums\UserRole;
use App\Support\PersianNormalizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = ['mobile', 'email', 'first_name', 'last_name', 'password', 'role', 'is_active', 'is_staff'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_staff' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'mobile';
    }

    public function setMobileAttribute(string $value): void
    {
        $this->attributes['mobile'] = PersianNormalizer::mobile($value);
    }

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->mobile;
    }

    public function profile(): HasOne { return $this->hasOne(Profile::class); }
    public function ads(): HasMany { return $this->hasMany(Ad::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }

    public function isModerator(): bool
    {
        return $this->is_staff && in_array($this->role, [UserRole::SuperAdmin, UserRole::Moderator], true);
    }
}
PHP,
'app/Models/Category.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['parent_id', 'title', 'slug', 'description', 'seo_title', 'seo_description', 'sort_order', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    public function ads(): HasMany { return $this->hasMany(Ad::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
PHP,
'app/Models/Country.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Country extends Model { protected $fillable = ['name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function provinces(): HasMany { return $this->hasMany(Province::class); } }
PHP,
'app/Models/Province.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Province extends Model { protected $fillable = ['country_id','name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function country(): BelongsTo { return $this->belongsTo(Country::class); } public function cities(): HasMany { return $this->hasMany(City::class); } }
PHP,
'app/Models/City.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class City extends Model { protected $fillable = ['province_id','name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function province(): BelongsTo { return $this->belongsTo(Province::class); } public function ads(): HasMany { return $this->hasMany(Ad::class); } }
PHP,
'app/Models/Profile.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Profile extends Model { protected $fillable = ['user_id','business_name','address','province_id','city_id','postal_code','avatar_path']; public function user(): BelongsTo { return $this->belongsTo(User::class); } public function province(): BelongsTo { return $this->belongsTo(Province::class); } public function city(): BelongsTo { return $this->belongsTo(City::class); } }
PHP,
'app/Models/Ad.php' => <<<'PHP'
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
    protected $fillable = ['code','slug','user_id','title','normalized_title','normalized_title_hash','description','normalized_description','normalized_description_hash','price','full_name','business_name','country_id','province_id','city_id','address','mobile_1','show_mobile_1','mobile_2','phone_1','phone_2','email','category_id','referrer','source','submit_ip','status','published_at','expires_at','sort_at','last_ladder_at','is_featured','is_colored','is_urgent','auto_ladder','deleted_at'];

    protected function casts(): array
    {
        return ['status' => AdStatus::class, 'price' => 'integer', 'published_at' => 'datetime', 'expires_at' => 'datetime', 'sort_at' => 'datetime', 'last_ladder_at' => 'datetime', 'deleted_at' => 'datetime', 'show_mobile_1' => 'boolean', 'is_featured' => 'boolean', 'is_colored' => 'boolean', 'is_urgent' => 'boolean', 'auto_ladder' => 'boolean'];
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

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('status', AdStatus::Active)->whereNull('deleted_at')->whereNotNull('published_at');
    }

    public function scopeOrderedForListing(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderByDesc('sort_at')->orderByDesc('published_at');
    }

    public function publicUrl(): string { return route('ads.show', ['ad' => $this->code, 'slug' => $this->slug]); }
    public function priceLabel(): string { return $this->price === null ? 'توافقی' : number_format($this->price).' ریال'; }
}
PHP,
'app/Models/AdImage.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class AdImage extends Model { protected $fillable = ['ad_id','image_thumb_path','image_display_path','width','height','sort_order','is_primary']; protected function casts(): array { return ['is_primary'=>'boolean']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function thumbUrl(): string { return Storage::disk('public')->url($this->image_thumb_path); } public function displayUrl(): string { return Storage::disk('public')->url($this->image_display_path); } }
PHP,
'app/Models/AdLink.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdLink extends Model { protected $fillable = ['ad_id','type','url','sort_order','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
PHP,
'app/Models/AdStatusHistory.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdStatusHistory extends Model { public $timestamps = false; protected $fillable = ['ad_id','from_status','to_status','changed_by','reason','created_at']; protected function casts(): array { return ['created_at'=>'datetime']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); } }
PHP,
'app/Models/ForbiddenWord.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ForbiddenWord extends Model { protected $fillable = ['word','normalized_word','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } }
PHP,
'app/Models/Tariff.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tariff extends Model { protected $fillable = ['code','title','description','price','service_type','duration_days','is_active','sort_order','settings']; protected function casts(): array { return ['is_active'=>'boolean','settings'=>'array']; } }
PHP,
'app/Models/Ticket.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Ticket extends Model { protected $fillable = ['user_id','subject','status','priority','closed_at']; protected function casts(): array { return ['closed_at'=>'datetime']; } public function user(): BelongsTo { return $this->belongsTo(User::class); } public function messages(): HasMany { return $this->hasMany(TicketMessage::class); } }
PHP,
'app/Models/TicketMessage.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TicketMessage extends Model { public $timestamps = false; protected $fillable = ['ticket_id','sender_id','message','created_at']; public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); } public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); } }
PHP,
'app/Support/PersianNormalizer.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class PersianNormalizer
{
    public static function text(?string $value): string
    {
        $value = trim((string) $value);
        $value = str_replace(['ي', 'ى', 'ك', "\u{00A0}", "\u{200C}"], ['ی', 'ی', 'ک', ' ', ' '], $value);
        return (string) preg_replace('/\s+/u', ' ', $value);
    }

    public static function mobile(string $value): string
    {
        $value = strtr(self::text($value), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        $value = preg_replace('/[^0-9+]/', '', $value) ?? '';
        $value = str_starts_with($value, '+98') ? '0'.substr($value, 3) : $value;
        $value = str_starts_with($value, '0098') ? '0'.substr($value, 4) : $value;
        if (!preg_match('/^09\d{9}$/', $value)) {
            throw new InvalidArgumentException('شمارهٔ موبایل معتبر نیست.');
        }
        return $value;
    }
}
PHP,
'app/Domains/Ads/Services/ForbiddenWordGuard.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Models\ForbiddenWord;
use App\Support\PersianNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class ForbiddenWordGuard
{
    public function assertAllowed(array $values): void
    {
        $words = Cache::remember('moderation.forbidden_words.v1', now()->addMinutes(10), fn () => ForbiddenWord::query()->where('is_active', true)->pluck('normalized_word')->all());
        $haystack = PersianNormalizer::text(implode(' ', array_filter($values)));
        foreach ($words as $word) {
            if ($word !== '' && mb_stripos($haystack, $word) !== false) {
                throw ValidationException::withMessages(['title' => 'متن آگهی شامل عبارت غیرمجاز است.']);
            }
        }
    }
}
PHP,
'app/Domains/Ads/Services/DuplicateDetector.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Models\Ad;
use App\Support\PersianNormalizer;

final class DuplicateDetector
{
    /** @return array{title:string,description:string,title_hash:string,description_hash:string} */
    public function fingerprints(string $title, string $description): array
    {
        $normalizedTitle = PersianNormalizer::text($title);
        $normalizedDescription = PersianNormalizer::text($description);
        return ['title' => $normalizedTitle, 'description' => $normalizedDescription, 'title_hash' => hash('sha256', $normalizedTitle), 'description_hash' => hash('sha256', $normalizedDescription)];
    }

    public function exists(array $fingerprints, ?int $exceptAdId = null): bool
    {
        return Ad::query()->whereNull('deleted_at')->when($exceptAdId, fn ($query) => $query->whereKeyNot($exceptAdId))->where('normalized_title_hash', $fingerprints['title_hash'])->where('normalized_description_hash', $fingerprints['description_hash'])->exists();
    }
}
PHP,
'app/Domains/Ads/Services/AdWorkflow.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\AdStatusHistory;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

final class AdWorkflow
{
    /** @var array<string, list<AdStatus>> */
    private const TRANSITIONS = [
        'draft' => [AdStatus::PendingApproval, AdStatus::Deleted],
        'pending_approval' => [AdStatus::Active, AdStatus::NeedsPermit, AdStatus::Inactive, AdStatus::Deleted],
        'active' => [AdStatus::PendingApproval, AdStatus::Inactive, AdStatus::Expired, AdStatus::Deleted],
        'needs_permit' => [AdStatus::PendingApproval, AdStatus::Active, AdStatus::Inactive, AdStatus::Deleted],
        'inactive' => [AdStatus::PendingApproval, AdStatus::Active, AdStatus::Deleted],
        'expired' => [AdStatus::Active, AdStatus::Deleted],
        'deleted' => [AdStatus::Inactive],
    ];

    public function transition(Ad $ad, AdStatus $target, ?User $actor = null, ?string $reason = null): Ad
    {
        return DB::transaction(function () use ($ad, $target, $actor, $reason): Ad {
            $locked = Ad::query()->lockForUpdate()->findOrFail($ad->id);
            $from = $locked->status;
            if ($from === $target) return $locked;
            if (!in_array($target, self::TRANSITIONS[$from->value] ?? [], true)) {
                throw new DomainException('تغییر وضعیت درخواستی مجاز نیست.');
            }
            $attributes = ['status' => $target];
            if ($target === AdStatus::Active) {
                $attributes['published_at'] ??= $locked->published_at ?? now();
                $attributes['sort_at'] = now();
                $attributes['expires_at'] ??= $locked->expires_at ?? now()->addDays((int) config('agahi.free_ad_duration_days', 30));
                $attributes['deleted_at'] = null;
            }
            if ($target === AdStatus::Expired) $attributes['expires_at'] = now();
            if ($target === AdStatus::Deleted) $attributes['deleted_at'] = now();
            $locked->forceFill($attributes)->save();
            AdStatusHistory::query()->create(['ad_id' => $locked->id, 'from_status' => $from->value, 'to_status' => $target->value, 'changed_by' => $actor?->id, 'reason' => $reason, 'created_at' => now()]);
            return $locked->refresh();
        });
    }
}
PHP,
'app/Domains/Ads/Services/AdImageProcessor.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Models\Ad;
use App\Models\AdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class AdImageProcessor
{
    public function store(Ad $ad, UploadedFile $upload, int $sortOrder = 0): AdImage
    {
        $maxBytes = (int) config('agahi.image_max_bytes', 5 * 1024 * 1024);
        $maxPixels = (int) config('agahi.image_max_pixels', 24_000_000);
        if (!$upload->isValid() || $upload->getSize() > $maxBytes) throw new RuntimeException('فایل تصویر معتبر نیست یا از حد مجاز بزرگ‌تر است.');
        $bytes = file_get_contents($upload->getRealPath());
        $source = $bytes === false ? false : @imagecreatefromstring($bytes);
        if ($source === false) throw new RuntimeException('فایل بارگذاری‌شده تصویر قابل پردازش نیست.');
        $width = imagesx($source); $height = imagesy($source);
        if ($width < 1 || $height < 1 || ($width * $height) > $maxPixels) { imagedestroy($source); throw new RuntimeException('ابعاد تصویر از حد مجاز بیشتر است.'); }
        $base = 'ads/'.now()->format('Y/m').'/'.$ad->code;
        Storage::disk('public')->makeDirectory($base);
        $displayPath = $base.'/'.bin2hex(random_bytes(12)).'-display.webp';
        $thumbPath = $base.'/'.bin2hex(random_bytes(12)).'-thumb.webp';
        $this->writeWebp($source, storage_path('app/public/'.$displayPath), 1280, 80);
        $this->writeWebp($source, storage_path('app/public/'.$thumbPath), 480, 78);
        imagedestroy($source);
        return AdImage::query()->create(['ad_id' => $ad->id, 'image_thumb_path' => $thumbPath, 'image_display_path' => $displayPath, 'width' => min($width, 1280), 'height' => min($height, 1280), 'sort_order' => $sortOrder, 'is_primary' => $sortOrder === 0]);
    }

    private function writeWebp(\GdImage $source, string $path, int $maximum, int $quality): void
    {
        $width = imagesx($source); $height = imagesy($source); $scale = min(1, $maximum / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale)); $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false); imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        if (!imagewebp($target, $path, $quality)) { imagedestroy($target); throw new RuntimeException('تولید نسخهٔ بهینهٔ تصویر ناموفق بود.'); }
        imagedestroy($target);
    }
}
PHP,
'app/Domains/Seo/SeoPolicy.php' => <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Domains\Seo;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use Illuminate\Support\Str;

final class SeoPolicy
{
    public function isIndexableAd(Ad $ad): bool { return $ad->status === AdStatus::Active && $ad->published_at !== null && $ad->deleted_at === null; }
    public function robotsForAd(Ad $ad): string { return $this->isIndexableAd($ad) ? 'index, follow' : 'noindex, follow'; }
    public function titleForAd(Ad $ad): string { return trim($ad->title.' در '.($ad->city?->name ?? 'ایران').' | '.config('app.name')); }
    public function descriptionForAd(Ad $ad): string { return Str::limit(trim(strip_tags($ad->description)), 155, '…'); }
    public function canonicalForAd(Ad $ad): string { return $ad->publicUrl(); }
    public function breadcrumbForAd(Ad $ad): array
    {
        $items = [['name' => 'خانه', 'url' => route('home')]];
        if ($ad->category) $items[] = ['name' => $ad->category->title, 'url' => route('categories.show', $ad->category)];
        $items[] = ['name' => $ad->title, 'url' => $ad->publicUrl()];
        return $items;
    }
}
PHP,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) {
        throw new RuntimeException("Cannot create directory for {$relative}");
    }
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." core PHP files.\n";
