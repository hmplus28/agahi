<?php

declare(strict_types=1);
namespace App\Domains\Ads\Services;
use Illuminate\Support\Facades\DB;

/**
 * Generates sequential numeric ad codes from the ad_code_counter row in
 * site_settings. Migration 2026_09_08_000001 changed ads.code to a bigint
 * column, so every code MUST be numeric (PostgreSQL rejects anything else
 * with SQLSTATE 22P02). Counter starts at 699999 => first code is 700000;
 * it is also raised to max(code) so it never lags behind migrated rows.
 */
final class AdCodeService {
    public const FIRST_CODE = 700000;

    public static function next(): int { return (int) DB::transaction(function (): int { $row=DB::table('site_settings')->where('key','ad_code_counter')->lockForUpdate()->first(); $highest=(int) (DB::table('ads')->max('code') ?? 0); if($row===null){ $current=max(self::FIRST_CODE-1,$highest); DB::table('site_settings')->insert(['key'=>'ad_code_counter','value'=>json_encode($current),'created_at'=>now(),'updated_at'=>now()]); } else { $current=max((int) json_decode((string) $row->value, true), $highest); } $next=$current+1; DB::table('site_settings')->where('key','ad_code_counter')->update(['value'=>json_encode($next),'updated_at'=>now()]); return $next; }); }
}
