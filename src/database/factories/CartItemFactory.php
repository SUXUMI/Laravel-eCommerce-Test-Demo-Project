<?php

namespace Database\Factories;

use App\Models\CartProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CartProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cart_id' => 1,
            'product_id' => 1,
            'quantity ' => $this->faker->numberBetween(1, 5),
        ];
    }
}
