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
        $source = $this->correctExifOrientation($source, $upload);
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

    private function correctExifOrientation(\GdImage $source, UploadedFile $upload): \GdImage
    {
        if ($upload->getMimeType() !== 'image/jpeg' || !function_exists('exif_read_data')) return $source;
        $exif = @exif_read_data($upload->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $result = $source;
        if (in_array($orientation, [2, 5, 7], true)) imageflip($result, IMG_FLIP_HORIZONTAL);
        if (in_array($orientation, [4], true)) imageflip($result, IMG_FLIP_VERTICAL);
        if (in_array($orientation, [3], true)) $result = imagerotate($result, 180, 0);
        if (in_array($orientation, [5, 6], true)) $result = imagerotate($result, -90, 0);
        if (in_array($orientation, [7, 8], true)) $result = imagerotate($result, 90, 0);
        if ($result !== $source) imagedestroy($source);
        return $result;
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
