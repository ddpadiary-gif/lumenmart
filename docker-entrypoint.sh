#!/usr/bin/env sh
set -e

# Jika DB_CONNECTION=sqlite (default Free Tier Render), siapkan file SQLite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE="${DB_DATABASE:-/tmp/lumenmart.sqlite}"
    touch "$DB_DATABASE"
fi

# Pastikan APP_KEY tersedia
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:t8BE1zDRoYytg2LSIzMTZS2bD4gnNBE3/B2LgOQHgwc="
fi

echo "==> Menjalankan migrasi database LUMEN MART..."
php artisan migrate --force

# Jalankan seeder otomatis jika tabel products masih kosong
PRODUCT_COUNT=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo \App\Models\Product::count();")
if [ "$PRODUCT_COUNT" = "0" ]; then
    echo "==> Menjalankan RetailSimulationSeeder (30 produk + 800 transaksi + Regresi + Apriori)..."
    php artisan db:seed --force
fi

echo "==> Menjalankan server LUMEN MART pada port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
