<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'product_id',
        'customer_id',
        'quantity',
        'unit_price',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'stock_applied',
        'ordered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'stock_applied' => 'boolean',
            'ordered_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Bank profile for this order's payment_method (ABA / ACLIDA / …). */
    public function paymentBank(): array
    {
        $banks = config('payment_banks', []);
        $key = strtolower((string) ($this->payment_method ?: 'aba'));

        return $banks[$key] ?? ($banks['aba'] ?? [
            'name' => strtoupper($key),
            'label' => strtoupper($key).' PAYMENT',
            'label_km' => '',
            'account_name' => '',
            'qr_image' => 'images/'.$key.'-payment-qr.png',
            'pay_url' => '#',
            'button' => 'Pay →',
            'color' => '#0369a1',
        ]);
    }
}
