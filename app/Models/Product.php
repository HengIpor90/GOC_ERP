<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'image',
        'price',
        'stock',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        $image = $this->getAttribute('image');

        if (is_string($image) && str_starts_with($image, 'uploads/products/')) {
            $path = public_path($image);

            if (is_file($path)) {
                return asset($image);
            }
        }

        return asset('images/product-placeholder.svg');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function reviews(): HasMany
{
    return $this->hasMany(ProductReview::class);
}

public function averageRating(): float
{
    return round((float) $this->reviews()->avg('rating'), 1);
}
}
