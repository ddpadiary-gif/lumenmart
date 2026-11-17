<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AprioriRule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'antecedent_product_ids', 'consequent_product_ids', 'antecedent_names', 'consequent_names',
        'support', 'confidence', 'lift_ratio', 'bundling_suggestion', 'bundle_discount_percent', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'antecedent_product_ids' => 'array',
            'consequent_product_ids' => 'array',
            'support' => 'float',
            'confidence' => 'float',
            'lift_ratio' => 'float',
            'bundle_discount_percent' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    /** Seluruh ID produk dalam aturan (antecedent ∪ consequent). */
    public function productIds(): array
    {
        $ids = array_map('intval', array_merge($this->antecedent_product_ids ?? [], $this->consequent_product_ids ?? []));
        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }
}
