<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactionStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_statuses',function(Blueprint $t){
            $t->bigIncrements('id');
            $t->bigInteger('transaction_id');
            $t->string('status');
            $t->string('description')->nullable();
            $t->timestamps();
            $t->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_statuses',function (Blueprint $t){
            $t->dropForeign('transaction_statuses_transaction_id_foreign');
        });
        Schema::drop('transaction_statuses');
    }
}
