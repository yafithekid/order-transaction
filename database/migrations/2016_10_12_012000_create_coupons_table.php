<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons',function (Blueprint $t){
            $t->bigIncrements('id');
            $t->string('code');
            $t->bigInteger('quantity');
            $t->double('percentage_cut')->nullable();
            $t->bigInteger('paid_cut')->nullable();
            $t->dateTime('valid_from');
            $t->dateTime('valid_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coupons');
    }
}
