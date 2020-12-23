<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('client_id');
            $table->string('bank', 50);
            $table->string('deposit_method', 50);
            $table->float('amount', 22, 4);
            $table->string('reference_number', 50);
            $table->string('product_name', 50);
            $table->string('bonus', 50);
            $table->string('receipt_url', 255);
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
        Schema::dropIfExists('new_transactions');
    }
}
