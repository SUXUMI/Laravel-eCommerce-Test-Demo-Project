<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cart_id');
            $table->foreign('cart_id', 'cart_products_cart_id')
                ->references('id')->on('carts')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id', 'cart_products_product_id')
                ->references('id')->on('products')->cascadeOnDelete()->cascadeOnUpdate();

            $table->decimal('quantity', 15, 4)->index('quantity');
            // $table->json('options');

            // $table->timestamps();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->index('created_at');
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->index('updated_at');

            $table->unique(['cart_id', 'product_id'], 'unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_products');
    }
}
