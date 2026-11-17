<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('target_period', 7); // format YYYY-MM
            $table->string('regression_equation');
            $table->decimal('predicted_demand', 12, 2);
            $table->decimal('mae', 12, 4);
            $table->decimal('rmse', 12, 4);
            $table->decimal('r_squared', 8, 4);
            $table->integer('restock_recommendation')->default(0);
            $table->timestamp('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_predictions');
    }
};
