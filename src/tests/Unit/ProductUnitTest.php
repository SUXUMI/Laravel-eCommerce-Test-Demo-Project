<?php

namespace Tests\Unit;

use App\Models\Product;
use Database\Factories\ProductFactory;
// use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitTest extends TestCase
{
    // use RefreshDatabase;

    public function test_product_can_be_created()
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(Product::class, $product);
    }

    public function test_product_can_be_soft_deleted()
    {
        $product = Product::factory()->create();

        $product->delete();

        $this->assertNotNull($product->deleted_at);
    }

    public function test_product_slug_reset_on_soft_delete()
    {
        $initial_slug = uniqid('test-slug-');

        $product = Product::factory()->create(['slug' => $initial_slug]);

        $product->delete();

        $this->assertNull($product->slug);

        $this->assertEquals($product->slug_before_delete, $initial_slug);
    }

    public function test_product_quantity_increment()
    {
        $initial_quantity = rand(1, 100);

        $increment = rand(1, 10);

        $product = Product::factory()->create(['quantity' => $initial_quantity]);

        $product->increaseQuantity($increment);

        return $this->assertEquals($initial_quantity + $increment, $product->quantity);
    }

    public function test_product_quantity_decrement()
    {
        $initial_quantity = rand(1, 100);

        $decrement = rand(1, 10);

        $product = Product::factory()->create(['quantity' => $initial_quantity]);

        $product->decreaseQuantity($decrement);

        return $this->assertEquals($initial_quantity - $decrement, $product->quantity);
    }
}
