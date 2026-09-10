<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_ads', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->json('ad_data');
            $table->string('status', 20)->default('pending'); // pending, completed, expired
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_ads');
    }
};
