<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 190)->unique();
            $table->text('description')->nullable();
            $table->string('seo_title', 180)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['parent_id', 'is_active', 'sort_order']);
        });

        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 160)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('provinces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 160);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['country_id', 'slug']);
            $table->index(['country_id', 'is_active', 'sort_order']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->string('name', 120);
            $table->string('slug', 160);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['province_id', 'slug']);
            $table->index(['province_id', 'is_active', 'sort_order']);
        });

        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name', 160)->nullable();
            $table->string('address', 500)->nullable();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('postal_code', 20)->nullable();
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('forbidden_words', function (Blueprint $table): void {
            $table->id();
            $table->string('word', 190);
            $table->string('normalized_word', 190)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('ads', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 24)->unique();
            $table->string('slug', 190)->index();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('title', 300);
            $table->string('normalized_title', 300)->index();
            $table->char('normalized_title_hash', 64)->index();
            $table->text('description');
            $table->text('normalized_description');
            $table->char('normalized_description_hash', 64)->index();
            $table->unsignedBigInteger('price')->nullable();
            $table->string('full_name', 160)->nullable();
            $table->string('business_name', 160)->nullable()->index();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 500)->nullable();
            $table->string('mobile_1', 15);
            $table->boolean('show_mobile_1')->default(true);
            $table->string('mobile_2', 15)->nullable();
            $table->string('phone_1', 30)->nullable();
            $table->string('phone_2', 30)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('referrer', 160)->nullable();
            $table->string('source', 30)->default('user_panel')->index();
            $table->string('status', 30)->default('draft')->index();
            $table->ipAddress('submit_ip')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sort_at')->nullable();
            $table->timestamp('last_ladder_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_colored')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('auto_ladder')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index(['status', 'category_id']);
            $table->index(['status', 'city_id']);
            $table->index(['category_id', 'city_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index(['is_featured', 'sort_at']);
        });

        Schema::create('ad_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('image_thumb_path');
            $table->string('image_display_path');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();
            $table->index(['ad_id', 'sort_order']);
        });

        Schema::create('ad_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('website');
            $table->string('url', 2048);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['ad_id', 'is_active', 'sort_order']);
        });

        Schema::create('ad_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 1000)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['ad_id', 'created_at']);
        });

        Schema::create('ad_permits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('permit_number', 160)->nullable();
            $table->string('issuer', 190)->nullable();
            $table->date('issued_at')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('admin_note', 1500)->nullable();
            $table->timestamps();
        });

        Schema::create('ad_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('description')->nullable();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('reporter_ip')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->string('admin_note', 1500)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['ad_id', 'status']);
        });

        Schema::create('tariffs', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->string('service_type', 40)->index();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('ad_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->constrained()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('status', 30)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['ad_id', 'status']);
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 160);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('total_price');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number', 32)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('title', 160);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('total_price');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method', 30);
            $table->string('status', 30)->default('pending')->index();
            $table->string('gateway', 60)->nullable();
            $table->string('authority', 190)->nullable()->unique();
            $table->string('reference_id', 190)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('admin_note', 1500)->nullable();
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('subject', 190);
            $table->string('status', 30)->default('open')->index();
            $table->string('priority', 20)->default('normal');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sms_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mobile', 15);
            $table->string('type', 60);
            $table->string('idempotency_key', 128)->unique();
            $table->string('provider_id', 190)->nullable();
            $table->string('status', 30)->index();
            $table->timestamp('sent_at')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 100)->index();
            $table->string('auditable_type', 190);
            $table->unsignedBigInteger('auditable_id');
            $table->json('context')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        foreach ([
            'audit_logs', 'sms_logs', 'ticket_messages', 'tickets', 'payments', 'invoice_items', 'invoices',
            'order_items', 'orders', 'ad_services', 'tariffs', 'ad_reports', 'ad_permits', 'ad_status_histories',
            'ad_links', 'ad_images', 'ads', 'forbidden_words', 'site_settings', 'cities', 'provinces', 'countries',
            'categories', 'profiles',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
