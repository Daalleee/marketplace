<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'price',
        'condition',
        'description',
        'image',
        'status',
        'stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relasi ke user (pemilik produk)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke kategori
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke item keranjang
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Relasi ke item pesanan
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}