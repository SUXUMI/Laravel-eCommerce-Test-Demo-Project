<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_id',
        'cost',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'payment_id' => 'integer',
        'cost' => 'float',
    ];

    public function products() {
        return $this->hasMany(OrderProduct::class, 'order_id');
    }
}
