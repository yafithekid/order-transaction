<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
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
        Schema::create('transactions',function (Blueprint $t){
            $t->bigIncrements('id');
            $t->string('shipping_id');
            $t->unsignedBigInteger('customer_id');
            $t->string('payment_url')->nullable();
            $t->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');

            $t->unique('shipping_id');
            $t->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions',function(Blueprint $t){
            $t->dropForeign('transactions_customer_id_foreign');
        });
        Schema::drop('transactions');
    }
}
