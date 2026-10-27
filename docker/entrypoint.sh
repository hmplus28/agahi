#!/bin/sh
set -e

cd /var/www/html

# ── fresh manifests: the committed ones may reference dev packages ──
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# ── APP_KEY: require or generate ──
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    export APP_KEY
    echo "WARNING: APP_KEY was empty - generated an EPHEMERAL key."
    echo "         Sessions/encrypted data break on container recreate."
    echo "         Set a fixed APP_KEY in your .env file."
fi

# ── wait for PostgreSQL ──
echo "Waiting for database ${DB_HOST}:${DB_PORT} ..."
tries=0
until php -r '
    try {
        new PDO(
            sprintf("pgsql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "5432", getenv("DB_DATABASE")),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
            [PDO::ATTR_TIMEOUT => 3]
        );
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
' >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "ERROR: database not reachable after 60 attempts." >&2
        exit 1
    fi
    sleep 2
done
echo "Database is up."

if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
    if [ "$RUN_SEEDER" = "true" ]; then
        echo "Seeding database..."
        php artisan db:seed --force
    fi
else
    # scheduler / one-off containers: block until migrations exist
    tries=0
    until php -r '
        try {
            $pdo = new PDO(
                sprintf("pgsql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "5432", getenv("DB_DATABASE")),
                getenv("DB_USERNAME"),
                getenv("DB_PASSWORD")
            );
            $pdo->query("SELECT 1 FROM migrations LIMIT 1");
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    ' >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "WARNING: migrations table not found after 60 attempts, continuing." >&2
            break
        fi
        sleep 2
    done
fi

# ── storage symlink (idempotent) ──
php artisan storage:link >/dev/null 2>&1 || true

# ── production caches ──
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
    php artisan view:cache
fi

echo "Starting: $*"
exec "$@"
