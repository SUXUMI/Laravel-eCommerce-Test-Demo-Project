<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'price' => 'float',
        'quantity' => 'float',
        'enabled' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleted(function(self $model) {
            $model->update([
                'slug_before_delete' => $model['slug'],
                'slug' => null,
            ]);
        });

        static::updating(function(self $model) {
            if ($model->quantity < 0) {
                throw new \InvalidArgumentException(__('Quantity can\'t be negative value.'));
            }
        });
    }

    public function increaseQuantity(int $quantity = 1)
    {
        return $this->increment('quantity', $quantity);
    }

    public function decreaseQuantity(int $quantity = 1) {
        return $this->decrement('quantity', $quantity);
    }

    /**
     * Use for object comparison
     *
     * @param Product $other
     * @return bool
     */
    public function equals(self $other): bool
    {
        return (bool)($this->id === $other->id);
    }
}
