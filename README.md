# LUMEN MART — Smart Retail E-Commerce & Intelligent Inventory (Laravel 13)

**Tagline:** *"Smart Daily Essentials, Intelligently Stocked"*

Sistem Informasi Inventory & E-Commerce UMKM Ritel Cerdas berbasis **Laravel 13**, **PHP 8.2+**, **MySQL (XAMPP)**, **Laravel Breeze (Blade + Tailwind CSS + Alpine.js)**, **Chart.js**, dan **Barryvdh/laravel-dompdf** dengan integrasi **Regresi Linear (OLS 80:20)** dan **Algoritma Apriori** (*Pure PHP Service Class*).

---

## 1. Instalasi & Menjalankan Proyek

```bash
# 1. Instal dependensi PHP (termasuk DomPDF jika belum terpasang)
composer install
composer require barryvdh/laravel-dompdf

# 2. Salin konfigurasi environment & generate app key
cp .env.example .env
php artisan key:generate

# 3. Jalankan migrasi & seeder simulasi (30 produk + 800 transaksi 12 bulan + Regresi Linear + Apriori)
php artisan migrate:fresh --seed

# 4. Instal & build aset frontend (Tailwind CSS + Alpine.js)
npm install
npm run build

# 5. Jalankan pengujian otomatis
php artisan test

# 6. Jalankan server pengembangan lokal
php artisan serve
```

---

## 2. Akun Demo Siap Pakai

| Peran | Email | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@lumenmart.test` | `password` | Dashboard Eksekutif, Lonceng Stok Kritis, CRUD Kategori & Produk, Transaksi Barang Masuk (*Restock*), POS Kasir Multi-Item, Manajemen Pesanan E-Commerce, Eksekusi Regresi Linear & Apriori, Ekspor CSV/PDF, Reset Simulasi |
| **Pemilik** | `pemilik@lumenmart.test` | `password` | *Read-only* Dashboard Analitik, Lonceng Stok Kritis, Hasil Prediksi Regresi Linear, Rekomendasi *Bundling* Apriori, dan Ekspor Laporan CSV/PDF |
| **Pelanggan** | `pelanggan@lumenmart.test` | `password` | Katalog Publik, Pencarian & Filter Instan, Quick View Modal, Wishlist/Favorit, Keranjang (*Smart Bundle Discount*), Checkout (COD / Transfer Bank / QRIS Simulasi), Pelacakan Pesanan & Unduh Invoice PDF |

---

## 3. Struktur Folder Gambar Produk Lokal (`public/assets/products/`)

Seluruh produk mereferensikan path gambar lokal `assets/products/<slug-kategori>/<nama-file>.jpg`:
- `public/assets/products/sembako/`
- `public/assets/products/minuman-kopi/`
- `public/assets/products/makanan-instan/`
- `public/assets/products/bumbu-dapur/`
- `public/assets/products/kebersihan-rumah/`

Apabila file `.jpg` belum ditempatkan pada folder tersebut, komponen kartu produk otomatis menampilkan **placeholder SVG minimalis bergaya Apple** (anti-*broken-image*).
