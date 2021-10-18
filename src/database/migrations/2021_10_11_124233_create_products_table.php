<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique('slug')->nullable();
            $table->string('slug_before_delete')->index('slug_before_delete')->nullable();
            $table->string('sku')->index('sku')->nullable();
            $table->decimal('price', 15, 4)->index('price')->nullable();
            $table->decimal('quantity', 15, 4)->index('quantity')->default(0);
            $table->string('name')->index('name');
            $table->string('description')->nullable();
            $table->dateTime('published_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->index('published_at');
            $table->boolean('enabled')->index('enabled')->default(1);

            // override default $table->timestamps();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'))->index('created_at');
            $table->timestamp('updated_at')->default(\DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->index('updated_at');
            $table->softDeletes('deleted_at', 0)->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
