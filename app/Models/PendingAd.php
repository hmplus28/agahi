<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingAd extends Model
{
    public $timestamps = false;

    protected $fillable = ['token', 'ad_data', 'status', 'created_at', 'expires_at'];

    protected $casts = [
        'ad_data' => 'array',
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
