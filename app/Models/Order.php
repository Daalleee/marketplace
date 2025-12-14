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
        'user_id',
        'order_number',
        'midtrans_order_id',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function getStatusInIndonesianAttribute()
    {
        $statusMap = [
            'pending' => 'Belum Lunas',
            'waiting_payment' => 'Menunggu Pembayaran',
            'challenge' => 'Belum Lunas',
            'confirmed' => 'Lunas',
            'shipped' => 'Lunas',
            'delivered' => 'Lunas',
            'failed' => 'Belum Lunas',
            'expired' => 'Belum Lunas',
            'cancelled' => 'Belum Lunas',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    public function getPaymentStatusAttribute()
    {
        if (in_array($this->status, ['confirmed', 'shipped', 'delivered'])) {
            return 'Lunas';
        } elseif (in_array($this->status, ['waiting_payment'])) {
            return 'Menunggu Pembayaran';
        } else {
            return 'Belum Lunas';
        }
    }

    // Relasi ke user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke item pesanan
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}