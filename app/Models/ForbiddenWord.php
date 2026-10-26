<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ForbiddenWord extends Model { protected $fillable = ['word','normalized_word','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } }
