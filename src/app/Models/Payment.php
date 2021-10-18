<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'float',
    ];

    protected static function booted()
    {
        parent::booted();

        /**
         * Update User's balance on new payment
         */
        static::created(function(self $model) {
            $user = User::findOrFail($model->user_id);
            $user->balance += $model->amount;
            $user->save();
        });
    }
}
