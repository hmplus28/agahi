<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Support\PersianNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class PersianNormalizerTest extends TestCase { #[Test] public function it_normalizes_arabic_letters_and_whitespace(): void { $this->assertSame('کالا ی تست',PersianNormalizer::text(" كالا   ي\u{200C}تست ")); } #[Test] public function it_normalizes_mobile_digits(): void { $this->assertSame('09123456789',PersianNormalizer::mobile('۰۹۱۲۳۴۵۶۷۸۹')); } #[Test] public function it_rejects_an_invalid_mobile(): void { $this->expectException(InvalidArgumentException::class); PersianNormalizer::mobile('123'); } }
