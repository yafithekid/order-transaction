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
            $t->unsignedBigInteger('coupon_id')->nullable();
            $t->string('shipping_id')->nullable();
            $t->unsignedBigInteger('customer_id');
            $t->string('payment_url')->nullable();
            $t->string('customer_name')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->boolean('submitted')->default(false);

            $t->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('cascade');
            $t->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade')->onUpdate('cascade');

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
            $t->dropForeign('transactions_coupon_id_foreign');
            $t->dropForeign('transactions_customer_id_foreign');
        });
        Schema::drop('transactions');
    }
}
