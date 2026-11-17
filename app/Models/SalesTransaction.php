<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SalesTransaction extends Model
{
    public const STATUSES = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
    ];

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'cod' => 'COD (Bayar di Tempat)',
        'transfer_bank' => 'Transfer Bank',
        'qris' => 'QRIS',
    ];

    public const CHANNELS = [
        'pos_toko' => 'POS Toko',
        'ecommerce' => 'E-Commerce',
    ];

    protected $fillable = [
        'invoice_number', 'user_id', 'channel', 'payment_method', 'order_status',
        'discount_amount', 'total_amount', 'shipping_address', 'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'invoice_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesTransactionDetail::class);
    }

    public static function generateInvoiceNumber(?string $prefix = 'INV'): string
    {
        do {
            $number = sprintf('%s-%s-%s', $prefix, now()->format('Ymd'), Str::upper(Str::random(5)));
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->order_status] ?? $this->order_status;
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function statusStep(): int
    {
        return array_search($this->order_status, array_keys(self::STATUSES), true) ?: 0;
    }

    public function subtotalAmount(): float
    {
        return (float) $this->total_amount + (float) $this->discount_amount;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->order_status) {
            'menunggu_pembayaran' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'diproses' => 'bg-blue-50 text-[#0071e3] ring-blue-200',
            'dikirim' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            default => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        };
    }
}
