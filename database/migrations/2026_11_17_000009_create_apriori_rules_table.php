<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apriori_rules', function (Blueprint $table) {
            $table->id();
            $table->json('antecedent_product_ids');
            $table->json('consequent_product_ids');
            $table->string('antecedent_names');
            $table->string('consequent_names');
            $table->decimal('support', 8, 4);     // persen (%)
            $table->decimal('confidence', 8, 4);  // persen (%)
            $table->decimal('lift_ratio', 8, 4);
            $table->string('bundling_suggestion');
            $table->unsignedTinyInteger('bundle_discount_percent')->default(5);
            $table->timestamp('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apriori_rules');
    }
};
