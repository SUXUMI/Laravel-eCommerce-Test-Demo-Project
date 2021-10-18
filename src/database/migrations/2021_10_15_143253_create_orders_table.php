<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id', 'order_user_id')
                ->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('payment_id')->nullable();
            $table->foreign('payment_id', 'order_payment_id')
                ->references('id')->on('payments')->restrictOnDelete()->cascadeOnUpdate();

            $table->decimal('cost', 15, 4)->index('cost')->nullable();

            $table->enum('status', \App\Enums\OrderStatus::getValues())->default(\App\Enums\OrderStatus::Ordered);

            // $table->timestamps();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->index('created_at');
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
