<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('channel', ['pos_toko', 'ecommerce']);
            $table->enum('payment_method', ['tunai', 'cod', 'transfer_bank', 'qris']);
            $table->enum('order_status', ['menunggu_pembayaran', 'diproses', 'dikirim', 'selesai'])->default('menunggu_pembayaran');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->text('shipping_address')->nullable();
            $table->date('transaction_date');
            $table->timestamps();

            $table->index(['channel', 'order_status']);
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
