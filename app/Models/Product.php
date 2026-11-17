<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'name', 'slug', 'description', 'image_path', 'unit',
        'purchase_price', 'selling_price', 'current_stock', 'min_stock_threshold',
        'movement_type', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'current_stock' => 'integer',
            'min_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function salesDetails(): HasMany
    {
        return $this->hasMany(SalesTransactionDetail::class);
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(IncomingTransaction::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function latestPrediction(): HasOne
    {
        return $this->hasOne(StockPrediction::class)->latestOfMany('generated_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock_threshold');
    }

    public function isCritical(): bool
    {
        return $this->current_stock <= $this->min_stock_threshold;
    }

    public function hasImage(): bool
    {
        return filled($this->image_path) && file_exists(public_path($this->image_path));
    }

    public function imageUrl(): ?string
    {
        return $this->hasImage() ? asset($this->image_path) : null;
    }

    public function isFastMoving(): bool
    {
        return $this->movement_type === 'fast_moving';
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->filter(fn ($w) => preg_match('/^[A-Za-z]/', $w))
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->take(2)
            ->implode('');
    }
}
