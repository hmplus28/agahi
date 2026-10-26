<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_permits', function (Blueprint $table): void {
            $table->text('permit_description')->nullable()->after('issued_at');
            $table->string('image2_path')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('ad_permits', function (Blueprint $table): void {
            $table->dropColumn(['permit_description', 'image2_path']);
        });
    }
};
