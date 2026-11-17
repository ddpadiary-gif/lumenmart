<?php

namespace Tests\Feature;

use App\Models\AprioriRule;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\StockPrediction;
use App\Models\User;
use App\Services\AprioriService;
use App\Services\LinearRegressionService;
use App\Services\SimulationDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LumenMartSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_breeze_registration_assigns_customer_role_by_default(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pelanggan Baru',
            'email' => 'baru@lumenmart.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'baru@lumenmart.test')->firstOrFail();
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue($user->isCustomer());
    }

    public function test_rbac_authorization_for_admin_pemilik_and_customer(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pemilik = User::factory()->create(['role' => User::ROLE_PEMILIK]);
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // Redirect /dashboard sesuai peran
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('backoffice.dashboard'));
        $this->actingAs($pemilik)->get('/dashboard')->assertRedirect(route('backoffice.dashboard'));
        $this->actingAs($customer)->get('/dashboard')->assertRedirect(route('home'));

        // Admin akses penuh ke Back-Office & operasional
        $this->actingAs($admin)->get(route('backoffice.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('backoffice.pos.index'))->assertOk();
        $this->actingAs($admin)->get(route('backoffice.products.index'))->assertOk();

        // Pemilik akses read-only ke analitik & laporan, namun 403 ke operasional Admin
        $this->actingAs($pemilik)->get(route('backoffice.dashboard'))->assertOk();
        $this->actingAs($pemilik)->get(route('backoffice.regression.index'))->assertOk();
        $this->actingAs($pemilik)->get(route('backoffice.apriori.index'))->assertOk();
        $this->actingAs($pemilik)->get(route('backoffice.reports.index'))->assertOk();
        $this->actingAs($pemilik)->get(route('backoffice.pos.index'))->assertForbidden();
        $this->actingAs($pemilik)->post(route('backoffice.simulation.reset'))->assertForbidden();

        // Customer akses storefront & keranjang, namun 403 ke Back-Office
        $this->actingAs($customer)->get(route('home'))->assertOk();
        $this->actingAs($customer)->get(route('cart.index'))->assertOk();
        $this->actingAs($customer)->get(route('backoffice.dashboard'))->assertForbidden();
    }

    public function test_ecommerce_checkout_applies_smart_bundle_discount_and_reduces_stock(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $category = Category::create(['name' => 'Minuman & Kopi', 'slug' => 'minuman-kopi']);

        $kopi = Product::create([
            'category_id' => $category->id,
            'sku' => 'LM-MNK-001',
            'name' => 'Kopi Bubuk Murni 200g',
            'slug' => 'kopi-bubuk-murni-200g',
            'image_path' => 'assets/products/minuman-kopi/kopi-bubuk-arabika-200g.jpg',
            'unit' => 'pack',
            'purchase_price' => 22000,
            'selling_price' => 30000,
            'current_stock' => 20,
            'min_stock_threshold' => 10,
            'movement_type' => 'fast_moving',
            'is_active' => true,
        ]);

        $gula = Product::create([
            'category_id' => $category->id,
            'sku' => 'LM-SMB-003',
            'name' => 'Gula Pasir Kristal Putih 1kg',
            'slug' => 'gula-pasir-kristal-putih-1kg',
            'image_path' => 'assets/products/sembako/gula-pasir-1kg.jpg',
            'unit' => 'pack',
            'purchase_price' => 15500,
            'selling_price' => 20000,
            'current_stock' => 20,
            'min_stock_threshold' => 10,
            'movement_type' => 'fast_moving',
            'is_active' => true,
        ]);

        // Aturan Apriori: Kopi + Gula diskon 10%
        $rule = AprioriRule::create([
            'antecedent_product_ids' => [$kopi->id],
            'consequent_product_ids' => [$gula->id],
            'antecedent_names' => $kopi->name,
            'consequent_names' => $gula->name,
            'support' => 14.5,
            'confidence' => 68.0,
            'lift_ratio' => 3.2,
            'bundling_suggestion' => 'Paket Hemat Kopi + Gula',
            'bundle_discount_percent' => 10,
            'generated_at' => now(),
        ]);

        // Tambahkan paket bundling ke keranjang pelanggan
        $this->actingAs($customer)
            ->post(route('cart.bundle', $rule))
            ->assertRedirect(route('cart.index'));

        // Checkout dengan metode QRIS
        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_address' => 'Jl. Melati No. 12, Kota Bandung 40121',
            'payment_method' => 'qris',
        ]);

        $order = SalesTransaction::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        // Harga 1 set = 30.000 + 20.000 = 50.000, diskon 10% = 5.000, total bayar = 45.000
        $this->assertEquals(5000.0, (float) $order->discount_amount);
        $this->assertEquals(45000.0, (float) $order->total_amount);
        $this->assertSame('menunggu_pembayaran', $order->order_status);

        // Stok kedua produk berkurang 1
        $this->assertSame(19, $kopi->fresh()->current_stock);
        $this->assertSame(19, $gula->fresh()->current_stock);

        // Unduh Invoice PDF berhasil
        $this->actingAs($customer)
            ->get(route('invoices.pdf', $order))
            ->assertOk();
    }

    public function test_admin_can_record_incoming_restock_transaction(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $category = Category::create(['name' => 'Sembako', 'slug' => 'sembako']);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'LM-SMB-001',
            'name' => 'Beras Pandan Wangi 5kg',
            'slug' => 'beras-pandan-wangi-5kg',
            'image_path' => 'assets/products/sembako/beras-pandan-wangi-5kg.jpg',
            'unit' => 'karung',
            'purchase_price' => 68000,
            'selling_price' => 76500,
            'current_stock' => 5,
            'min_stock_threshold' => 15,
            'movement_type' => 'fast_moving',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('backoffice.incoming.store'), [
                'product_id' => $product->id,
                'quantity' => 30,
                'unit_cost' => 69000,
                'transaction_date' => now()->toDateString(),
                'supplier_name' => 'PT Sumber Pangan Nusantara',
                'notes' => 'Restock stok kritis',
            ])
            ->assertRedirect(route('backoffice.incoming.index'));

        $this->assertSame(35, $product->fresh()->current_stock);
        $this->assertEquals(69000.0, (float) $product->fresh()->purchase_price);
        $this->assertDatabaseHas('incoming_transactions', [
            'product_id' => $product->id,
            'quantity' => 30,
            'supplier_name' => 'PT Sumber Pangan Nusantara',
        ]);
    }

    public function test_linear_regression_and_apriori_services_with_full_simulation(): void
    {
        /** @var SimulationDataService $simulation */
        $simulation = app(SimulationDataService::class);
        $stats = $simulation->generate(2026);

        $this->assertSame(30, $stats['products']);
        $this->assertSame(800, $stats['transactions']);
        $this->assertGreaterThan(2000, $stats['details']);

        // Pastikan seluruh produk fast-moving memiliki R² >= 0.70 pada stock_predictions
        $fastPredictions = StockPrediction::with('product')
            ->whereHas('product', fn ($q) => $q->where('movement_type', 'fast_moving'))
            ->get();

        $this->assertCount(15, $fastPredictions);
        foreach ($fastPredictions as $prediction) {
            $this->assertGreaterThanOrEqual(
                0.70,
                $prediction->r_squared,
                "R² untuk {$prediction->product->name} harus >= 0.70"
            );
        }

        // Pastikan Apriori menemukan aturan dengan lift_ratio > 1.0
        $rules = AprioriRule::all();
        $this->assertNotEmpty($rules);
        foreach ($rules as $rule) {
            $this->assertGreaterThan(1.0, $rule->lift_ratio);
            $this->assertGreaterThan(0, $rule->support);
            $this->assertGreaterThan(0, $rule->confidence);
        }

        // Unit check langsung pada LinearRegressionService & AprioriService
        $reg = app(LinearRegressionService::class);
        $analysis = $reg->analyze([10, 12, 15, 18, 20, 23, 25, 28, 30, 33, 35, 38], 10);
        $this->assertGreaterThan(0.98, $analysis['r_squared']);
        $this->assertGreaterThan(39, $analysis['predicted_demand']);
        $this->assertGreaterThan(0, $analysis['restock_recommendation']);

        $apriori = app(AprioriService::class);
        $mined = $apriori->mine([
            [1, 2, 3],
            [1, 2, 3],
            [1, 2],
            [1, 2],
            [3, 4],
            [4, 5],
        ], 0.25, 0.60);
        $this->assertNotEmpty($mined['rules']);
        $this->assertGreaterThan(1.0, $mined['rules'][0]['lift']);
    }
}
