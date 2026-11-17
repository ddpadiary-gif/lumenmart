<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPrediction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'target_period', 'regression_equation', 'predicted_demand',
        'mae', 'rmse', 'r_squared', 'restock_recommendation', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'predicted_demand' => 'float',
            'mae' => 'float',
            'rmse' => 'float',
            'r_squared' => 'float',
            'restock_recommendation' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
