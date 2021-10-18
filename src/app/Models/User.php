<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Prevent Balance negative value by throw an \InvalidArgumentException
        static::updating(function(self $model) {
            if ($model->balance < 0)
            {
                throw new \InvalidArgumentException(
                    __('Balance can\'t be negative. Withdraw amount can\'t exceed :max_amount',
                        ['max_amount' => $model->getOriginal('balance')]
                    )
                );
            }
        });
    }

    /**
     * Addition to User's Balance
     *
     * @param $amount - positive for addition, negative for subtraction
     */
    public function additionToBalance($amount)
    {
        $this->balance += (float)$amount;
    }
}
