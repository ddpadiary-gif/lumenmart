<?php

namespace App\Services;

use App\Models\AprioriRule;
use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use App\Models\StockPrediction;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Generator data simulasi LUMEN MART:
 *  - 3 akun demo + pelanggan simulasi
 *  - 5 kategori & 30 produk wajib
 *  - 800 transaksi penjualan 12 bulan (tren naik + noise, pola co-occurrence untuk Apriori)
 *  - riwayat barang masuk yang konsisten dengan stok akhir
 *  - menjalankan Regresi Linear & Apriori di akhir proses
 *
 * Tidak bergantung pada Faker sehingga tetap berjalan di server produksi (composer --no-dev).
 */
class SimulationDataService
{
    public const TOTAL_TRANSACTIONS = 800;

    public const MONTHS = 12;

    public const CATEGORIES = [
        'sembako' => ['Sembako', 'SMB', 'Kebutuhan pokok harian: beras, minyak, gula, telur, dan tepung.'],
        'minuman-kopi' => ['Minuman & Kopi', 'MNK', 'Kopi, teh, susu, air mineral, dan minuman segar.'],
        'makanan-instan' => ['Makanan Instan', 'MKI', 'Mie instan, roti, biskuit, sereal, dan makanan kaleng.'],
        'bumbu-dapur' => ['Bumbu Dapur', 'BMB', 'Kecap, saus, kaldu, dan rempah pelengkap masakan.'],
        'kebersihan-rumah' => ['Kebersihan Rumah', 'KBR', 'Sabun, deterjen, pewangi, dan pembersih rumah tangga.'],
    ];

    /**
     * [key, kategori, file, nama, movement, unit, harga beli, harga jual, probabilitas dasar per keranjang]
     * Probabilitas hanya dipakai untuk produk fast-moving (slow-moving memakai kuota bulanan tetap).
     */
    public const PRODUCTS = [
        ['beras', 'sembako', 'beras-pandan-wangi-5kg.jpg', 'Beras Pandan Wangi 5kg', 'fast_moving', 'karung', 68000, 76500, 0.13],
        ['minyak', 'sembako', 'minyak-goreng-2l.jpg', 'Minyak Goreng Sawit 2L', 'fast_moving', 'pouch', 32000, 36500, 0.18],
        ['gula', 'sembako', 'gula-pasir-1kg.jpg', 'Gula Pasir Kristal Putih 1kg', 'fast_moving', 'pack', 15500, 18000, 0.13],
        ['telur', 'sembako', 'telur-ayam-negeri-1kg.jpg', 'Telur Ayam Negeri Segar 1kg', 'fast_moving', 'kg', 26000, 29500, 0.11],
        ['terigu', 'sembako', 'tepung-terigu-1kg.jpg', 'Tepung Terigu Serbaguna 1kg', 'fast_moving', 'pack', 11000, 13500, 0.10],
        ['garam', 'sembako', 'garam-beryodium-500g.jpg', 'Garam Dapur Beryodium 500g', 'slow_moving', 'pack', 3500, 5000, 0],

        ['kopi', 'minuman-kopi', 'kopi-bubuk-arabika-200g.jpg', 'Kopi Bubuk Murni 200g', 'fast_moving', 'pack', 22000, 27500, 0.19],
        ['teh', 'minuman-kopi', 'teh-celup-isi-25.jpg', 'Teh Celup Melati Isi 25', 'fast_moving', 'box', 5500, 7500, 0.13],
        ['skm', 'minuman-kopi', 'susu-kental-manis-370g.jpg', 'Susu Kental Manis Kaleng 370g', 'fast_moving', 'kaleng', 10500, 12500, 0.10],
        ['air', 'minuman-kopi', 'air-mineral-1500ml.jpg', 'Air Mineral Kemasan 1500ml', 'fast_moving', 'botol', 4000, 6000, 0.18],
        ['sirup', 'minuman-kopi', 'sirup-cocopandan-460ml.jpg', 'Sirup Cocopandan Botol 460ml', 'slow_moving', 'botol', 15000, 19000, 0],
        ['soda', 'minuman-kopi', 'minuman-soda-kaleng-330ml.jpg', 'Minuman Soda Kaleng 330ml', 'slow_moving', 'kaleng', 6000, 8000, 0],

        ['mie_goreng', 'makanan-instan', 'mie-instan-goreng-85g.jpg', 'Mie Instan Goreng 85g', 'fast_moving', 'bungkus', 2700, 3500, 0.20],
        ['mie_kuah', 'makanan-instan', 'mie-instan-kuah-75g.jpg', 'Mie Instan Kuah Soto 75g', 'fast_moving', 'bungkus', 2500, 3300, 0.15],
        ['roti', 'makanan-instan', 'roti-tawar-kupas.jpg', 'Roti Tawar Kupas Lembut', 'fast_moving', 'bungkus', 13000, 16000, 0.12],
        ['biskuit', 'makanan-instan', 'biskuit-kaleng-300g.jpg', 'Biskuit Mentega Kaleng 300g', 'slow_moving', 'kaleng', 38000, 47000, 0],
        ['oat', 'makanan-instan', 'sereal-oat-instan-400g.jpg', 'Sereal Oat Instan 400g', 'slow_moving', 'box', 32000, 39500, 0],
        ['sarden', 'makanan-instan', 'sarden-kaleng-155g.jpg', 'Ikan Sarden Saus Tomat 155g', 'slow_moving', 'kaleng', 9000, 11500, 0],

        ['kecap', 'bumbu-dapur', 'kecap-manis-520ml.jpg', 'Kecap Manis Kedelai Hitam 520ml', 'fast_moving', 'botol', 19000, 23000, 0.13],
        ['sambal', 'bumbu-dapur', 'saus-sambal-335ml.jpg', 'Saus Sambal Pedas 335ml', 'fast_moving', 'botol', 9500, 12000, 0.12],
        ['tepung_bumbu', 'bumbu-dapur', 'tepung-bumbu-serbaguna-210g.jpg', 'Tepung Bumbu Krispi 210g', 'slow_moving', 'pack', 5500, 7000, 0],
        ['saus_tiram', 'bumbu-dapur', 'saus-tiram-270ml.jpg', 'Saus Tiram Botol 270ml', 'slow_moving', 'botol', 12000, 15500, 0],
        ['kaldu', 'bumbu-dapur', 'kaldu-jamur-100g.jpg', 'Kaldu Jamur Non-MSG 100g', 'slow_moving', 'pack', 9000, 12000, 0],
        ['lada', 'bumbu-dapur', 'lada-putih-bubuk-50g.jpg', 'Lada Putih Bubuk Murni 50g', 'slow_moving', 'pack', 6000, 8500, 0],

        ['sabun_mandi', 'kebersihan-rumah', 'sabun-mandi-cair-450ml.jpg', 'Sabun Mandi Cair 450ml', 'fast_moving', 'botol', 21000, 26500, 0.10],
        ['deterjen', 'kebersihan-rumah', 'deterjen-cair-750ml.jpg', 'Deterjen Cair Konsentrat 750ml', 'slow_moving', 'pouch', 18000, 23000, 0],
        ['sabun_piring', 'kebersihan-rumah', 'sabun-cuci-piring-650ml.jpg', 'Sabun Cuci Piring Jeruk Nipis 650ml', 'slow_moving', 'pouch', 12500, 16000, 0],
        ['pasta_gigi', 'kebersihan-rumah', 'pasta-gigi-190g.jpg', 'Pasta Gigi Herbal 190g', 'slow_moving', 'tube', 13000, 16500, 0],
        ['pewangi', 'kebersihan-rumah', 'pewangi-pakaian-800ml.jpg', 'Pewangi & Pelembut Pakaian 800ml', 'slow_moving', 'pouch', 14000, 18000, 0],
        ['pembersih_lantai', 'kebersihan-rumah', 'pembersih-lantai-780ml.jpg', 'Pembersih Lantai Karbol 780ml', 'slow_moving', 'pouch', 11000, 14500, 0],
    ];

    /** Pola pembelian berpasangan: [anchor, consequent, peluang]. */
    public const CO_OCCURRENCE = [
        ['kopi', 'gula', 0.65],
        ['mie_goreng', 'telur', 0.60],
        ['minyak', 'tepung_bumbu', 0.55],
        ['deterjen', 'pewangi', 0.55],
    ];

    /** Pola 3-itemset: keranjang berisi [a, b] → tambah c dengan peluang p. */
    public const TRIPLE_PATTERN = [['kopi', 'gula'], 'skm', 0.50];

    /** Stok akhir yang sengaja dibuat kritis: key => [stok akhir, ambang minimum]. */
    public const CRITICAL_STOCK = [
        'minyak' => [6, 15],
        'telur' => [4, 15],
        'kopi' => [5, 15],
        'garam' => [2, 8],
        'saus_tiram' => [3, 8],
        'pasta_gigi' => [4, 8],
    ];

    private const CUSTOMER_NAMES = [
        'Rina Pelanggan', 'Budi Santoso', 'Siti Aminah', 'Andi Pratama', 'Dewi Lestari', 'Rizky Ramadhan',
        'Nur Halimah', 'Agus Setiawan', 'Maya Sari', 'Fajar Nugroho', 'Intan Permata', 'Yoga Saputra', 'Lina Marlina',
    ];

    private const ADDRESSES = [
        'Jl. Melati No. 12, Kel. Sukamaju, Kec. Cibeunying, Bandung 40121',
        'Jl. Kenanga Raya No. 5, Kel. Tegalsari, Kec. Candisari, Semarang 50251',
        'Jl. Anggrek II No. 27, Kel. Pondok Kelapa, Kec. Duren Sawit, Jakarta Timur 13450',
        'Jl. Flamboyan No. 8, Kel. Sumbersari, Kec. Lowokwaru, Malang 65145',
        'Perum Griya Asri Blok C-14, Kel. Jatimulya, Kec. Tambun Selatan, Bekasi 17510',
        'Jl. Cempaka Putih No. 3, Kel. Gondokusuman, Yogyakarta 55224',
        'Jl. Mawar Merah No. 41, Kel. Rungkut Kidul, Kec. Rungkut, Surabaya 60293',
        'Jl. Dahlia No. 19, Kel. Pasar Minggu, Jakarta Selatan 12520',
        'Jl. Teratai No. 2A, Kel. Banyumanik, Semarang 50264',
        'Jl. Bougenville No. 33, Kel. Panjang Jiwo, Kec. Tenggilis, Surabaya 60299',
    ];

    private const SUPPLIERS = [
        'sembako' => 'PT Sumber Pangan Nusantara',
        'minuman-kopi' => 'CV Aroma Minuman Sejahtera',
        'makanan-instan' => 'PT Distribusi Pangan Instan',
        'bumbu-dapur' => 'CV Rasa Dapur Indonesia',
        'kebersihan-rumah' => 'PT Higienis Rumah Tangga',
    ];

    private Randomizer $rng;

    public function __construct(
        private LinearRegressionService $regression,
        private AprioriService $apriori,
    ) {}

    /**
     * Reset & generate ulang seluruh data simulasi.
     */
    public function generate(int $seed = 2026, bool $runAnalytics = true): array
    {
        $this->rng = new Randomizer(new Mt19937($seed));
        $today = CarbonImmutable::today();

        $stats = DB::transaction(function () use ($today) {
            $users = $this->ensureUsers();
            $this->wipe();
            $products = $this->createCatalog();
            $baskets = $this->buildBaskets($products, $today);
            $sold = $this->persistSales($baskets, $products, $users);
            $this->persistIncoming($products, $sold, $users['admin'], $today);
            $this->seedWishlist($users['customer'], $products);

            return [
                'products' => count($products),
                'transactions' => count($baskets),
                'details' => array_sum(array_map(fn ($b) => count($b['items']), $baskets)),
            ];
        });

        if ($runAnalytics) {
            $stats['predictions'] = $this->regression->generate();
            $stats['apriori'] = $this->apriori->generate();
        }

        return $stats;
    }

    /** @return array{admin: User, pemilik: User, customer: User, customers: array<int, User>} */
    private function ensureUsers(): array
    {
        $make = fn (string $email, string $name, string $role) => User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'role' => $role, 'password' => Hash::make('password'), 'email_verified_at' => now()],
        );

        $admin = $make('admin@lumenmart.test', 'Admin Lumen', User::ROLE_ADMIN);
        $pemilik = $make('pemilik@lumenmart.test', 'Pemilik Lumen', User::ROLE_PEMILIK);
        $customer = $make('pelanggan@lumenmart.test', self::CUSTOMER_NAMES[0], User::ROLE_CUSTOMER);

        $customers = [$customer];
        foreach (array_slice(self::CUSTOMER_NAMES, 1) as $i => $name) {
            $customers[] = User::firstOrCreate(
                ['email' => sprintf('pelanggan%02d@lumenmart.test', $i + 1)],
                ['name' => $name, 'role' => User::ROLE_CUSTOMER, 'password' => Hash::make('password'), 'email_verified_at' => now()],
            );
        }

        return compact('admin', 'pemilik', 'customer', 'customers');
    }

    private function wipe(): void
    {
        AprioriRule::query()->delete();
        StockPrediction::query()->delete();
        SalesTransactionDetail::query()->delete();
        SalesTransaction::query()->delete();
        IncomingTransaction::query()->delete();
        Wishlist::query()->delete();
        Product::query()->delete();
        Category::query()->delete();
    }

    /** @return array<string, Product> [key => Product] */
    private function createCatalog(): array
    {
        $categories = [];
        foreach (self::CATEGORIES as $slug => [$name, , $description]) {
            $categories[$slug] = Category::create(compact('name', 'slug', 'description'));
        }

        $products = [];
        $counter = [];
        foreach (self::PRODUCTS as [$key, $cat, $file, $name, $movement, $unit, $buy, $sell]) {
            $counter[$cat] = ($counter[$cat] ?? 0) + 1;
            $threshold = self::CRITICAL_STOCK[$key][1] ?? ($movement === 'fast_moving' ? 15 : 8);

            $products[$key] = Product::create([
                'category_id' => $categories[$cat]->id,
                'sku' => sprintf('LM-%s-%03d', self::CATEGORIES[$cat][1], $counter[$cat]),
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => sprintf(
                    '%s pilihan LUMEN MART untuk kebutuhan harian keluarga. Kualitas terjaga, harga bersahabat, dan selalu tersedia di rak %s.',
                    $name, self::CATEGORIES[$cat][0]
                ),
                'image_path' => "assets/products/{$cat}/{$file}",
                'unit' => $unit,
                'purchase_price' => $buy,
                'selling_price' => $sell,
                'current_stock' => 0,
                'min_stock_threshold' => $threshold,
                'movement_type' => $movement,
                'is_active' => true,
            ]);
        }

        return $products;
    }

    /**
     * Bangun 800 keranjang belanja selama 12 bulan.
     *
     * @return array<int, array{date: CarbonImmutable, items: array<string, int>}>
     */
    private function buildBaskets(array $products, CarbonImmutable $today): array
    {
        // Distribusi transaksi per bulan dengan tren naik (bobot 2..13 → ±18 s.d. ±115 transaksi/bulan).
        $weights = range(2, 1 + self::MONTHS);
        $sumW = array_sum($weights);
        $perMonth = array_map(fn ($w) => (int) round(self::TOTAL_TRANSACTIONS * $w / $sumW), $weights);
        $perMonth[self::MONTHS - 1] += self::TOTAL_TRANSACTIONS - array_sum($perMonth);

        $firstMonth = $today->startOfMonth()->subMonthsNoOverflow(self::MONTHS - 1);
        $fast = array_values(array_filter(self::PRODUCTS, fn ($p) => $p[4] === 'fast_moving'));
        $slow = array_values(array_filter(self::PRODUCTS, fn ($p) => $p[4] === 'slow_moving'));

        $all = [];
        foreach ($perMonth as $m => $count) {
            $monthStart = $firstMonth->addMonthsNoOverflow($m);
            $maxDay = $monthStart->isSameMonth($today) ? $today->day : $monthStart->daysInMonth;

            /** @var array<int, array<string,int>> $baskets */
            $baskets = array_fill(0, $count, []);
            $indices = range(0, $count - 1);

            // 1) Fast-moving: jumlah kemunculan ∝ jumlah transaksi bulan tsb (tren) ± noise 5%.
            foreach ($fast as $p) {
                $k = (int) round($count * $p[8] * $this->float(0.95, 1.05));
                foreach ($this->pick($indices, $k) as $i) {
                    $baskets[$i][$p[0]] = $this->qty(3);
                }
            }

            // 2) Slow-moving: kuota bulanan relatif datar.
            foreach ($slow as $p) {
                $k = $p[0] === 'deterjen' ? $this->rng->getInt(4, 6) : $this->rng->getInt(1, 3);
                foreach ($this->pick($indices, $k) as $i) {
                    $baskets[$i][$p[0]] ??= $this->qty(2);
                }
            }

            // 3) Injeksi co-occurrence untuk Apriori.
            foreach (self::CO_OCCURRENCE as [$anchor, $consequent, $prob]) {
                foreach ($baskets as $i => $items) {
                    if (isset($items[$anchor]) && ! isset($items[$consequent]) && $this->chance($prob)) {
                        $baskets[$i][$consequent] = $this->qty(2);
                    }
                }
            }
            [[$a, $b], $c, $prob] = self::TRIPLE_PATTERN;
            foreach ($baskets as $i => $items) {
                if (isset($items[$a], $items[$b]) && ! isset($items[$c]) && $this->chance($prob)) {
                    $baskets[$i][$c] = $this->qty(2);
                }
            }

            // 4) Keranjang kosong → isi 1 produk fast-moving acak.
            foreach ($baskets as $i => $items) {
                if ($items === []) {
                    $p = $fast[$this->rng->getInt(0, count($fast) - 1)];
                    $baskets[$i][$p[0]] = 1;
                }
            }

            foreach ($baskets as $items) {
                $date = $monthStart->setDay($this->rng->getInt(1, $maxDay))
                    ->setTime($this->rng->getInt(8, 20), $this->rng->getInt(0, 59), $this->rng->getInt(0, 59));
                $all[] = ['date' => $date, 'items' => $items];
            }
        }

        usort($all, fn ($x, $y) => $x['date'] <=> $y['date']);

        return $all;
    }

    /**
     * Simpan transaksi penjualan + detail. Mengembalikan total terjual per produk per bulan.
     *
     * @return array<string, array<string, int>> [key => ['Y-m' => qty]]
     */
    private function persistSales(array $baskets, array $products, array $users): array
    {
        $today = CarbonImmutable::today();
        $sold = [];
        $details = [];
        $seq = 0;

        foreach ($baskets as $basket) {
            $seq++;
            /** @var CarbonImmutable $date */
            $date = $basket['date'];
            $isPos = $this->chance(0.55);
            $recent = abs($date->diffInDays($today)) <= 6;

            if ($isPos) {
                $channel = 'pos_toko';
                $payment = $this->chance(0.7) ? 'tunai' : 'qris';
                $status = 'selesai';
                $userId = $users['admin']->id;
                $address = null;
            } else {
                $channel = 'ecommerce';
                $roll = $this->rng->getFloat(0, 1);
                $payment = $roll < 0.40 ? 'cod' : ($roll < 0.75 ? 'transfer_bank' : 'qris');
                $status = 'selesai';
                if ($recent) {
                    $options = $payment === 'cod'
                        ? ['diproses', 'dikirim', 'selesai']
                        : ['menunggu_pembayaran', 'diproses', 'dikirim', 'selesai'];
                    $status = $options[$this->rng->getInt(0, count($options) - 1)];
                }
                $customer = $users['customers'][$this->rng->getInt(0, count($users['customers']) - 1)];
                $userId = $customer->id;
                $address = self::ADDRESSES[$this->rng->getInt(0, count(self::ADDRESSES) - 1)];
            }

            $total = 0;
            foreach ($basket['items'] as $key => $qty) {
                $total += $products[$key]->selling_price * $qty;
            }

            $transactionId = DB::table('sales_transactions')->insertGetId([
                'invoice_number' => sprintf('%s-%s-%04d', $isPos ? 'POS' : 'INV', $date->format('Ymd'), $seq),
                'user_id' => $userId,
                'channel' => $channel,
                'payment_method' => $payment,
                'order_status' => $status,
                'discount_amount' => 0,
                'total_amount' => $total,
                'shipping_address' => $address,
                'transaction_date' => $date->toDateString(),
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($basket['items'] as $key => $qty) {
                $price = (float) $products[$key]->selling_price;
                $details[] = [
                    'sales_transaction_id' => $transactionId,
                    'product_id' => $products[$key]->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $price * $qty,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
                $month = $date->format('Y-m');
                $sold[$key][$month] = ($sold[$key][$month] ?? 0) + $qty;
            }
        }

        foreach (array_chunk($details, 500) as $chunk) {
            DB::table('sales_transaction_details')->insert($chunk);
        }

        return $sold;
    }

    /**
     * Barang masuk bulanan = kebutuhan bulan tsb, ditambah stok akhir pada restock terakhir.
     * Hasilnya: stok sistem = Σ masuk − Σ keluar (konsisten).
     */
    private function persistIncoming(array $products, array $sold, User $admin, CarbonImmutable $today): void
    {
        $rows = [];
        $firstMonth = $today->startOfMonth()->subMonthsNoOverflow(self::MONTHS - 1);
        $categorySlugs = Category::query()->pluck('slug', 'id')->all();

        foreach ($products as $key => $product) {
            $final = isset(self::CRITICAL_STOCK[$key])
                ? self::CRITICAL_STOCK[$key][0]
                : $this->rng->getInt($product->min_stock_threshold * 2, $product->min_stock_threshold * 5);

            $category = $categorySlugs[$product->category_id] ?? null;

            for ($m = 0; $m < self::MONTHS; $m++) {
                $month = $firstMonth->addMonthsNoOverflow($m);
                $qty = $sold[$key][$month->format('Y-m')] ?? 0;
                if ($m === self::MONTHS - 1) {
                    $qty += $final;
                }
                if ($qty <= 0) {
                    continue;
                }
                $date = $month->startOfMonth();
                $rows[] = [
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'quantity' => $qty,
                    'unit_cost' => $product->purchase_price,
                    'transaction_date' => $date->toDateString(),
                    'supplier_name' => self::SUPPLIERS[$category] ?? 'Distributor Umum',
                    'notes' => 'Restock rutin bulan '.$month->translatedFormat('F Y'),
                    'created_at' => $date->setTime(7, 30),
                    'updated_at' => $date->setTime(7, 30),
                ];
            }

            $product->update(['current_stock' => $final]);
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('incoming_transactions')->insert($chunk);
        }
    }

    private function seedWishlist(User $customer, array $products): void
    {
        foreach (['kopi', 'biskuit', 'oat'] as $key) {
            Wishlist::firstOrCreate(['user_id' => $customer->id, 'product_id' => $products[$key]->id]);
        }
    }

    // ---------------------------------------------------------------- helpers

    private function pick(array $indices, int $k): array
    {
        $k = max(0, min($k, count($indices)));

        return $k === 0 ? [] : array_slice($this->rng->shuffleArray($indices), 0, $k);
    }

    private function chance(float $p): bool
    {
        return $this->rng->getFloat(0, 1) < $p;
    }

    private function float(float $min, float $max): float
    {
        return $this->rng->getFloat($min, $max);
    }

    /** Kuantitas acak 1..max dengan bobot menurun. */
    private function qty(int $max): int
    {
        $roll = $this->rng->getFloat(0, 1);

        return match ($max) {
            1 => 1,
            2 => $roll < 0.7 ? 1 : 2,
            default => $roll < 0.45 ? 1 : ($roll < 0.80 ? 2 : 3),
        };
    }
}
