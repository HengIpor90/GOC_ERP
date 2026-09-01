<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sale_type',
        'sale_quantity',
        'units_per_pack',
        'quantity',
        'unit_price',
        'discount_rate',
        'subtotal',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'sale_quantity' => 'integer',
            'units_per_pack' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleTypeLabel(): string
    {
        return match ($this->sale_type) {
            'wholesale' => 'Wholesale / Bulk',
            'retail' => 'Retail',
            default => 'General',
        };
    }

    public function sellingUnitLabel(): string
    {
        return $this->sale_type === 'wholesale'
            ? "pack ({$this->units_per_pack} units)"
            : 'unit';
    }
}
