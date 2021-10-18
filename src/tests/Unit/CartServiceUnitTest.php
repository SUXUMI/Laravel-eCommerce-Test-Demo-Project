<?php

namespace Tests\Unit;

use App\Models\Product;
// use PHPUnit\Framework\TestCase;
use App\Services\CartService;
use Tests\TestCase;

class CartServiceUnitTest extends TestCase
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var int
     */
    protected $productStockQuantity = 10;

    /**
     * @var float
     */
    protected $productPrice = 5.0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton('cart', function($app) {
            return new CartService(time());
        });

        $this->product = Product::factory()->create([
            'price' => $this->productPrice,
            'quantity' => $this->productStockQuantity,
        ]);
    }

    public function test_cart_product_can_be_added()
    {
        $cartQuantity = 3;

        \Cart::add($this->product, $cartQuantity);

        // assert existence
        $this->assertTrue(\Cart::has($this->product));

        // assert items count
        $this->assertEquals(\Cart::products($this->product)->count(), 1);

        // assert product object
        $this->assertObjectEquals($this->product, \Cart::get($this->product)->product);
    }

    public function test_cart_product_quantity_cant_exceed_stock_quantity()
    {
        $cartQuantity = $this->productStockQuantity + 1;

        \Cart::add($this->product, $cartQuantity);

        $this->assertEquals(\Cart::get($this->product)->quantity, $this->productStockQuantity);
    }

    public function test_cart_product_can_be_updated()
    {
        \Cart::add($this->product, $this->productStockQuantity);
        \Cart::add($this->product, $this->productStockQuantity - 3);

        $this->assertEquals(\Cart::get($this->product)->quantity, $this->productStockQuantity - 3);
    }

    public function test_cart_product_can_be_removed()
    {
        \Cart::add($this->product, $this->productStockQuantity);
        \Cart::remove($this->product);

        $this->assertFalse(\Cart::has($this->product));
    }

    public function test_cart_products_cost()
    {
        \Cart::add($this->product, $this->productStockQuantity);

        $this->assertEquals(\Cart::cost(), $this->product->price * $this->productStockQuantity);
    }

    public function test_cart_can_be_cleaned()
    {
        \Cart::clear();

        \Cart::add($this->product, $this->productStockQuantity);

        $this->assertEquals(\Cart::products()->count(), 1);

        \Cart::clear();

        $this->assertEquals(\Cart::products()->count(), 0);
    }

    public function test_cart_can_be_destroyed()
    {
        \Cart::add($this->product, $this->productStockQuantity);

        $this->assertEquals(\Cart::products()->count(), 1);

        \Cart::destroy();

        $this->assertDatabaseMissing('carts', ['owner_id' => \Cart::getOwnerId()]);
    }
}
