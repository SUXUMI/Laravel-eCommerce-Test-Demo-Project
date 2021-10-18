<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderServiceUnitTest extends TestCase
{
    // use RefreshDatabase;

    protected $user;

    protected $products;

    protected $cart;

    protected $cartProductQuantity = 2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['balance' => 0]);

        $this->products = collect([
            Product::factory()->create(['price' => 8, 'quantity' => 10]),
            Product::factory()->create(['price' => 12, 'quantity' => 10]),
        ]);

        $this->cart = resolve('cart');

        $this->products->each(function($product) {
            $this->cart->add($product, $this->cartProductQuantity);
        });

        // this doesn't trigger successful login event
        // $this->actingAs($this->user);

        Auth::login($this->user);
    }

    public function test_order_creation_based_on_auth_user_cart()
    {
        $order = resolve('order')->createOrder(resolve('cart'));

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'user_id' => $this->user->id,
            'status' => OrderStatus::Ordered,
        ]);

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_order_products_quantity_deduction()
    {
        $order = resolve('order')->createOrder(resolve('cart'));

        // assert stock quantities
        $this->products->each(function($product) {
            $expectedQuantity = $product->quantity - $this->cartProductQuantity;

            $product->refresh();

            $this->assertEquals($expectedQuantity, $product->quantity);
        });
    }

    public function test_order_creation_based_on_some_user_s_cart()
    {
        $testUser = User::factory()->create(['balance' => 100.00]);

        $cart = new CartService($testUser->id);
        $cart->add(Product::factory()->create(['price' => 7, 'quantity' => 10]), 1);
        $cart->add(Product::factory()->create(['price' => 5, 'quantity' => 10]), 2);

        $order = (new OrderService())->createOrder($cart);

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'user_id' => $testUser->id,
            'status' => OrderStatus::Ordered,
        ]);

        // in this case RefreshDatabase is required!
        // $this->assertDatabaseCount(OrderProduct::class, 2);

        $this->assertEquals(2, Order::find($order->id)->products->count());
    }

    public function test_order_creation_failure_based_on_cart_without_items()
    {
        $testUser = User::factory()->create(['balance' => 100.00]);

        $cart = new CartService($testUser->id);

        $order = (new OrderService())->createOrder($cart);

        $this->assertFalse($order);
    }

    public function test_order_payment_failure_on_insufficient_funds()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->user->balance = 0;
        $this->user->save();

        $order = resolve('order')->createOrder(resolve('cart'));

        resolve('order')->payOrder($order);
    }

    // exact match run:  php artisan test --filter '/::test_order_payment$/'
    public function test_order_payment_success()
    {
        $this->user->balance = 0;
        $this->user->save();

        $initialBalance = 100;

        // add some balance
        resolve('payment')->deposit($initialBalance);

        $this->user->refresh();

        // assert initial balance
        $this->assertEquals($initialBalance, $this->user->balance);

        $order = resolve('order')->createOrder(resolve('cart'));

        resolve('order')->payOrder($order);

        $this->user->refresh();

        // assert left balance
        $this->assertEquals($initialBalance - $order->cost, $this->user->balance);

        // assert order final status
        $this->assertEquals(OrderStatus::Paid, Order::find($order->id)->status);
    }

    public function test_order_return_including_balance_refund_and_quantity_return()
    {
        $this->user->balance = 0;
        $this->user->save();

        $initialBalance = 100;
        resolve('payment')->deposit($initialBalance);
        $order = resolve('order')->createOrder(resolve('cart'));

        // refresh current products' quantities
        $this->products->each(function($product) { $product->refresh(); });

        resolve('order')->payOrder($order);
        resolve('order')->returnOrder($order);

        $this->user->refresh();

        // assert refunded balance
        $refundedBalance = ($initialBalance - $order->cost) + $order->cost * (1 - (float)config('ecommerce.order.refund.penalty'));
        $this->assertEquals($refundedBalance, $this->user->balance);

        // assert order final status
        $this->assertEquals(OrderStatus::Returned, Order::find($order->id)->status);

        // assert stock quantities
        $this->products->each(function($product) {
            $expectedQuantity = $product->quantity + $this->cartProductQuantity;

            $product->refresh();

            $this->assertEquals($expectedQuantity, $product->quantity);
        });
    }
}
