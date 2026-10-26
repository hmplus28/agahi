<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->string('hamrah_1', 500)->nullable()->after('phone_2');
            $table->string('hamrah_2', 500)->nullable()->after('hamrah_1');
            $table->string('sobit_1', 500)->nullable()->after('hamrah_2');
            $table->string('sobit_2', 500)->nullable()->after('sobit_1');
            $table->json('keywords')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table): void {
            $table->dropColumn(['hamrah_1', 'hamrah_2', 'sobit_1', 'sobit_2', 'keywords']);
        });
    }
};
