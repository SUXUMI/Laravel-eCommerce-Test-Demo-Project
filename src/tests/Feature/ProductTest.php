<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_product_cant_be_created_by_guest()
    {
        $response = $this->call('POST', route('admin.products.store'), []);

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }

    public function test_product_cant_be_created_by_non_admin_user()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->call('POST', route('admin.products.store'), []);

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(302);
    }

    public function test_product_can_be_created_by_admin()
    {
        // $this->withoutExceptionHandling();

        $user = User::factory()->create(['is_admin' => 1]);

        $this->actingAs($user);

        $payload = Product::factory()->make()->toArray();

        $response = $this->call('POST', route('admin.products.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $this->assertDatabaseHas('products', ['slug' => $payload['slug']]);
    }

    public function test_product_store_validation()
    {
        // $this->withoutExceptionHandling();

        $user = User::factory()->create(['is_admin' => 1]);

        $this->actingAs($user);

        $response = $this->call('POST', route('admin.products.store'), []);

        $response->assertSessionHasErrors();
    }
}
