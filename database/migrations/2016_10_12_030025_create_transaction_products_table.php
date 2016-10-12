<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactionProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_products',function(Blueprint $t){
            $t->bigIncrements('id');
            $t->unsignedBigInteger('transaction_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('quantity');

            $t->unique(['transaction_id','product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transaction_products');
    }
}
