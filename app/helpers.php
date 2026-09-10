<?php

/**
 * Convert Latin digits in a string to Persian digits.
 */
function to_persian_digits(string $value): string
{
    return strtr($value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}

/**
 * ─── مجموعه توابع تاریخ شمسی ───────────────────────── */
/**
 * سال شمسی متناظر با سال میلادی.
 */
function jalali_year(?int $gregorianYear = null): int
{
    $year  = $gregorianYear ?? (int) date('Y');
    $month = (int) date('n', $gregorianYear ? (int) strtotime("$gregorianYear-06-01") : time());
    return $month >= 4 ? $year - 621 : $year - 622;
}

/**
 * تاریخ شمسی کامل (Y/m/d).
 *
 * @param  \DateTimeInterface|string|null  $date
 */
function jdate($date = null, string $format = 'YYYY/MM/DD'): string
{
    [$y, $m, $d, $H, $i, $s] = explode_date_parts($date);

    return str_replace(
        ['YYYY', 'YY', 'MM', 'DD', 'HH', 'II', 'SS'],
        [$y, substr($y, -2), str_pad($m, 2, '0', STR_PAD_LEFT), str_pad($d, 2, '0', STR_PAD_LEFT), str_pad($H, 2, '0', STR_PAD_LEFT), str_pad($i, 2, '0', STR_PAD_LEFT), str_pad($s, 2, '0', STR_PAD_LEFT)],
        $format
    );
}

/**
 * نمایش نسبی (مانند «۳ روز پیش»).
 *
 * @param  \DateTimeInterface|string|null  $date
 */
function jdate_human($date = null): string
{
    if (! $date) {
        return '—';
    }
    if (! $date instanceof \DateTimeInterface) {
        $date = new \DateTimeImmutable($date);
    }

    $diff = (int) round((time() - $date->getTimestamp()) / 60);

    if ($diff < 1) {
        return 'همین الان';
    }
    if ($diff < 60) {
        return "{$diff} دقیقه پیش";
    }
    $hours = (int) round($diff / 60);
    if ($hours < 24) {
        return "{$hours} ساعت پیش";
    }
    $days = (int) round($diff / 1440);
    if ($days < 30) {
        return "{$days} روز پیش";
    }
    $months = (int) round($days / 30);
    if ($months < 12) {
        return "{$months} ماه پیش";
    }

    return (int) round($months / 12) . ' سال پیش';
}

// ──────────── internal ────────────

function explode_date_parts($date): array
{
    if ($date instanceof \DateTimeInterface) {
        $ts = $date->getTimestamp();
    } elseif (is_string($date)) {
        $ts = strtotime($date);
    } else {
        $ts = time();
    }

    $gy = (int) date('Y', $ts);
    $gm = (int) date('n', $ts);
    $gd = (int) date('j', $ts);
    $H  = (int) date('H', $ts);
    $i  = (int) date('i', $ts);
    $s  = (int) date('s', $ts);

    [$jy, $jm, $jd] = gregorian_to_jalali($gy, $gm, $gd);

    return [$jy, $jm, $jd, $H, $i, $s];
}

/**
 * الگوریتم دقیق میلادی به هجری شمسی
 */
function gregorian_to_jalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2  = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + (int) (($gy2 + 3) / 4) - (int) (($gy2 + 99) / 100) + (int) (($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
    $jy   = -1595 + (33 * (int) ($days / 12053));
    $days %= 12053;
    $jy   += 4 * (int) ($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += (int) (($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + (int) ($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + (int) (($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [(int) $jy, (int) $jm, (int) $jd];
}