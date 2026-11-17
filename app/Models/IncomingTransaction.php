<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingTransaction extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'quantity', 'unit_cost', 'transaction_date', 'supplier_name', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function totalCost(): float
    {
        return (float) $this->unit_cost * $this->quantity;
    }
}
