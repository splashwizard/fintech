<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewTransactionWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_transaction_withdraw', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('bank_id');
            $table->float('amount', 22, 4);
            $table->integer('product_id');
            $table->string('remark', 50);
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
        Schema::dropIfExists('new_transaction_withdraw');
    }
}
