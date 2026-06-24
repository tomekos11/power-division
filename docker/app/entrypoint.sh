#!/bin/sh
set -e

cd /var/www/html

echo "Waiting for PostgreSQL..."
until php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        exit(0);
    } catch (Throwable \$e) {
        exit(1);
    }
" 2>/dev/null; do
    sleep 2
done
echo "PostgreSQL is ready."

TEST_DB="${DB_DATABASE}_test"
export TEST_DB

echo "Ensuring test database exists (${TEST_DB})..."
php -r "
    \$testDb = getenv('TEST_DB');
    if (! preg_match('/^[a-zA-Z0-9_]+$/', \$testDb)) {
        throw new InvalidArgumentException('Invalid test database name.');
    }
    try {
        \$pdo = new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=postgres',
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        \$pdo->exec('CREATE DATABASE ' . \$testDb);
    } catch (Throwable \$e) {
        if (! str_contains(\$e->getMessage(), 'already exists')) {
            throw \$e;
        }
    }
"

echo "Waiting for Redis..."
until php -r "
    try {
        \$redis = new Redis();
        \$redis->connect(getenv('REDIS_HOST') ?: 'redis', (int) (getenv('REDIS_PORT') ?: 6379));
        exit(0);
    } catch (Throwable \$e) {
        exit(1);
    }
" 2>/dev/null; do
    sleep 2
done
echo "Redis is ready."

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R a+rwx storage bootstrap/cache 2>/dev/null || true

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# Mounted volume may be owned by host user; allow git inside container for Composer.
git config --global --add safe.directory /var/www/html 2>/dev/null || true

echo "Running composer install..."
if ! composer install --no-interaction --prefer-dist --optimize-autoloader; then
    echo "composer.lock out of sync — running composer update..."
    composer update --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --no-interaction

exec "$@"
