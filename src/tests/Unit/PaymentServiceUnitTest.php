<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use App\Models\User;
use Tests\TestCase;

class PaymentServiceUnitTest extends TestCase
{
    public function test_payment_deposit_on_non_auth_user()
    {
        $this->expectException(\InvalidArgumentException::class);

        resolve('payment')->deposit(5.0);
    }

    public function test_payment_deposit_on_non_exiting_user()
    {
        $this->expectException(\Exception::class);

        $this->actingAs(new User(['id' => 1000]));

        resolve('payment')->deposit(5.0);
    }

    public function test_payment_deposit_on_auth_user()
    {
        $user = User::factory()->create();

        $amount = 5.0;

        $this->actingAs($user);

        resolve('payment')->deposit($amount);

        $user->refresh();

        $this->assertEquals($amount, $user->balance);
    }

    public function test_payment_withdraw_on_auth_user()
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->actingAs($user);

        resolve('payment')->withdraw(15);

        $user->refresh();

        $this->assertEquals(100.00 - 15, $user->balance);
    }

    public function test_payment_withdraw_on_auth_user_exceeding_balance()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create(['balance' => 100.00]);

        $this->actingAs($user);

        resolve('payment')->withdraw(200);
    }

    public function test_payment_withdraw_on_auth_user_with_invalid_amount()
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create(['balance' => 100.00]);

        $this->actingAs($user);

        resolve('payment')->withdraw(-1);
    }

    public function test_payment_refund_on_auth_user()
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->actingAs($user);

        resolve('payment')->refund(100);

        $user->refresh();

        $this->assertEquals(200, $user->balance);
    }
}
