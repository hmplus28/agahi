<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure the site_settings row for ad_code_counter exists
        $exists = DB::table('site_settings')->where('key', 'ad_code_counter')->exists();
        if (!$exists) {
            DB::table('site_settings')->insert([
                'key' => 'ad_code_counter',
                'value' => json_encode(699999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // SQLite doesn't support dropColumn on indexed columns well, so we rebuild the table
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // Disable foreign keys for the table rebuild
            DB::statement('PRAGMA foreign_keys = OFF');

            // For SQLite: create new table, copy data, drop old, rename
            DB::statement('CREATE TABLE ads_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code INTEGER NOT NULL UNIQUE,
                slug VARCHAR(190) NOT NULL,
                user_id INTEGER NOT NULL,
                title VARCHAR(300) NOT NULL,
                normalized_title VARCHAR(300) NOT NULL,
                normalized_title_hash CHAR(64) NOT NULL,
                description TEXT NOT NULL,
                normalized_description TEXT NOT NULL,
                normalized_description_hash CHAR(64) NOT NULL,
                price INTEGER,
                full_name VARCHAR(160),
                business_name VARCHAR(160),
                country_id INTEGER,
                province_id INTEGER,
                city_id INTEGER,
                address VARCHAR(500),
                mobile_1 VARCHAR(15) NOT NULL,
                show_mobile_1 BOOLEAN DEFAULT 1,
                mobile_2 VARCHAR(15),
                phone_1 VARCHAR(30),
                phone_2 VARCHAR(30),
                hamrah_1 VARCHAR(500),
                hamrah_2 VARCHAR(500),
                sobit_1 VARCHAR(500),
                sobit_2 VARCHAR(500),
                email VARCHAR(255),
                keywords JSON,
                category_id INTEGER NOT NULL,
                referrer VARCHAR(160),
                source VARCHAR(30) DEFAULT \'user_panel\',
                status VARCHAR(30) DEFAULT \'draft\',
                submit_ip VARCHAR(45),
                published_at TIMESTAMP,
                expires_at TIMESTAMP,
                sort_at TIMESTAMP,
                last_ladder_at TIMESTAMP,
                views_count INTEGER DEFAULT 0,
                is_featured BOOLEAN DEFAULT 0,
                is_colored BOOLEAN DEFAULT 0,
                is_urgent BOOLEAN DEFAULT 0,
                auto_ladder BOOLEAN DEFAULT 0,
                deleted_at TIMESTAMP,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )');

            // Copy data with numeric codes
            DB::statement('INSERT INTO ads_new (id, code, slug, user_id, title, normalized_title, normalized_title_hash, description, normalized_description, normalized_description_hash, price, full_name, business_name, country_id, province_id, city_id, address, mobile_1, show_mobile_1, mobile_2, phone_1, phone_2, hamrah_1, hamrah_2, sobit_1, sobit_2, email, keywords, category_id, referrer, source, status, submit_ip, published_at, expires_at, sort_at, last_ladder_at, views_count, is_featured, is_colored, is_urgent, auto_ladder, deleted_at, created_at, updated_at) SELECT id, id + 699999, slug, user_id, title, normalized_title, normalized_title_hash, description, normalized_description, normalized_description_hash, price, full_name, business_name, country_id, province_id, city_id, address, mobile_1, show_mobile_1, mobile_2, phone_1, phone_2, hamrah_1, hamrah_2, sobit_1, sobit_2, email, keywords, category_id, referrer, source, status, submit_ip, published_at, expires_at, sort_at, last_ladder_at, views_count, is_featured, is_colored, is_urgent, auto_ladder, deleted_at, created_at, updated_at FROM ads');

            DB::statement('DROP TABLE ads');
            DB::statement('ALTER TABLE ads_new RENAME TO ads');

            // Recreate indexes
            DB::statement('CREATE INDEX ads_slug_index ON ads (slug)');
            DB::statement('CREATE INDEX ads_normalized_title_index ON ads (normalized_title)');
            DB::statement('CREATE INDEX ads_normalized_title_hash_index ON ads (normalized_title_hash)');
            DB::statement('CREATE INDEX ads_normalized_description_hash_index ON ads (normalized_description_hash)');
            DB::statement('CREATE INDEX ads_status_published_at_index ON ads (status, published_at)');
            DB::statement('CREATE INDEX ads_status_category_id_index ON ads (status, category_id)');
            DB::statement('CREATE INDEX ads_status_city_id_index ON ads (status, city_id)');
            DB::statement('CREATE INDEX ads_category_id_city_id_status_index ON ads (category_id, city_id, status)');
            DB::statement('CREATE INDEX ads_user_id_status_index ON ads (user_id, status)');
            DB::statement('CREATE INDEX ads_expires_at_status_index ON ads (expires_at, status)');
            DB::statement('CREATE INDEX ads_is_featured_sort_at_index ON ads (is_featured, sort_at)');
            DB::statement('CREATE INDEX ads_business_name_index ON ads (business_name)');
            DB::statement('CREATE INDEX ads_source_index ON ads (source)');
            DB::statement('CREATE INDEX ads_status_index ON ads (status)');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            // For PostgreSQL/MySQL: standard approach
            Schema::table('ads', function (Blueprint $table): void {
                $table->unsignedBigInteger('new_code')->unique()->after('id');
            });

            $ads = DB::table('ads')->select('id', 'code')->get();
            foreach ($ads as $ad) {
                $numericCode = 700000 + $ad->id;
                DB::table('ads')->where('id', $ad->id)->update(['new_code' => $numericCode]);
            }

            $maxCode = DB::table('ads')->max('new_code');
            if ($maxCode) {
                DB::table('site_settings')->where('key', 'ad_code_counter')->update([
                    'value' => json_encode($maxCode),
                ]);
            }

            Schema::table('ads', function (Blueprint $table): void {
                $table->dropIndex('ads_code_unique');
                $table->dropColumn('code');
            });

            Schema::table('ads', function (Blueprint $table): void {
                $table->renameColumn('new_code', 'code');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('CREATE TABLE ads_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code VARCHAR(24) NOT NULL UNIQUE,
                slug VARCHAR(190) NOT NULL,
                user_id INTEGER NOT NULL,
                title VARCHAR(300) NOT NULL,
                normalized_title VARCHAR(300) NOT NULL,
                normalized_title_hash CHAR(64) NOT NULL,
                description TEXT NOT NULL,
                normalized_description TEXT NOT NULL,
                normalized_description_hash CHAR(64) NOT NULL,
                price INTEGER,
                full_name VARCHAR(160),
                business_name VARCHAR(160),
                country_id INTEGER,
                province_id INTEGER,
                city_id INTEGER,
                address VARCHAR(500),
                mobile_1 VARCHAR(15) NOT NULL,
                show_mobile_1 BOOLEAN DEFAULT 1,
                mobile_2 VARCHAR(15),
                phone_1 VARCHAR(30),
                phone_2 VARCHAR(30),
                hamrah_1 VARCHAR(500),
                hamrah_2 VARCHAR(500),
                sobit_1 VARCHAR(500),
                sobit_2 VARCHAR(500),
                email VARCHAR(255),
                keywords JSON,
                category_id INTEGER NOT NULL,
                referrer VARCHAR(160),
                source VARCHAR(30) DEFAULT \'user_panel\',
                status VARCHAR(30) DEFAULT \'draft\',
                submit_ip VARCHAR(45),
                published_at TIMESTAMP,
                expires_at TIMESTAMP,
                sort_at TIMESTAMP,
                last_ladder_at TIMESTAMP,
                views_count INTEGER DEFAULT 0,
                is_featured BOOLEAN DEFAULT 0,
                is_colored BOOLEAN DEFAULT 0,
                is_urgent BOOLEAN DEFAULT 0,
                auto_ladder BOOLEAN DEFAULT 0,
                deleted_at TIMESTAMP,
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )');

            DB::statement("INSERT INTO ads_old (id, code, slug, user_id, title, normalized_title, normalized_title_hash, description, normalized_description, normalized_description_hash, price, full_name, business_name, country_id, province_id, city_id, address, mobile_1, show_mobile_1, mobile_2, phone_1, phone_2, hamrah_1, hamrah_2, sobit_1, sobit_2, email, keywords, category_id, referrer, source, status, submit_ip, published_at, expires_at, sort_at, last_ladder_at, views_count, is_featured, is_colored, is_urgent, auto_ladder, deleted_at, created_at, updated_at) SELECT id, CAST(code AS VARCHAR(24)), slug, user_id, title, normalized_title, normalized_title_hash, description, normalized_description, normalized_description_hash, price, full_name, business_name, country_id, province_id, city_id, address, mobile_1, show_mobile_1, mobile_2, phone_1, phone_2, hamrah_1, hamrah_2, sobit_1, sobit_2, email, keywords, category_id, referrer, source, status, submit_ip, published_at, expires_at, sort_at, last_ladder_at, views_count, is_featured, is_colored, is_urgent, auto_ladder, deleted_at, created_at, updated_at FROM ads");

            DB::statement('DROP TABLE ads');
            DB::statement('ALTER TABLE ads_old RENAME TO ads');

            // Recreate indexes
            DB::statement('CREATE INDEX ads_slug_index ON ads (slug)');
            DB::statement('CREATE INDEX ads_normalized_title_index ON ads (normalized_title)');
            DB::statement('CREATE INDEX ads_normalized_title_hash_index ON ads (normalized_title_hash)');
            DB::statement('CREATE INDEX ads_normalized_description_hash_index ON ads (normalized_description_hash)');
            DB::statement('CREATE INDEX ads_status_published_at_index ON ads (status, published_at)');
            DB::statement('CREATE INDEX ads_status_category_id_index ON ads (status, category_id)');
            DB::statement('CREATE INDEX ads_status_city_id_index ON ads (status, city_id)');
            DB::statement('CREATE INDEX ads_category_id_city_id_status_index ON ads (category_id, city_id, status)');
            DB::statement('CREATE INDEX ads_user_id_status_index ON ads (user_id, status)');
            DB::statement('CREATE INDEX ads_expires_at_status_index ON ads (expires_at, status)');
            DB::statement('CREATE INDEX ads_is_featured_sort_at_index ON ads (is_featured, sort_at)');
            DB::statement('CREATE INDEX ads_business_name_index ON ads (business_name)');
            DB::statement('CREATE INDEX ads_source_index ON ads (source)');
            DB::statement('CREATE INDEX ads_status_index ON ads (status)');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('ads', function (Blueprint $table): void {
                $table->string('code', 24)->unique()->after('id');
            });

            $ads = DB::table('ads')->select('id', 'code')->get();
            foreach ($ads as $ad) {
                DB::table('ads')->where('id', $ad->id)->update(['code' => (string) $ad->code]);
            }

            Schema::table('ads', function (Blueprint $table): void {
                $table->dropColumn('new_code');
            });
        }

        DB::table('site_settings')->where('key', 'ad_code_counter')->delete();
    }
};
