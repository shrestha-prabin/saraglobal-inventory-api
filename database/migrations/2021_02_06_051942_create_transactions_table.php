<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('seller_user_id')->references('id')->on('users');
            $table->foreignId('buyer_user_id')->references('id')->on('users');
            $table->foreignId('product_id')->references('id')->on('products');
            $table->unsignedInteger('count');
            $table->decimal('amount', 8, 2);
            $table->string('remarks', 1000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
