<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tariff extends Model { protected $fillable = ['code','title','description','price','service_type','duration_days','is_active','sort_order','settings']; protected function casts(): array { return ['is_active'=>'boolean','settings'=>'array']; } }
