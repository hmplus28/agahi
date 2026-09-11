<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_expiry_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->timestamp('scheduled_at')->comment('زمان ارسال پیامک (۱ سال پس از ثبت آگهی)');
            $table->timestamp('sent_at')->nullable()->comment('زمان ارسال واقعی پیامک');
            $table->string('status', 20)->default('pending')->index();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_expiry_reminders');
    }
};
