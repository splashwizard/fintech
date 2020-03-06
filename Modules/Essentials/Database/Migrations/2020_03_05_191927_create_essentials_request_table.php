<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEssentialsRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('essentials_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('essentials_request_type_id')->nullable()->index();
            $table->integer('business_id')->index();
            $table->integer('user_id')->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('ref_no')->nullable();
            $table->enum('status', ['pending', 'approved', 'cancelled'])->nullable();
            $table->text('reason')->nullable();
            $table->text('status_note')->nullable();
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
        Schema::dropIfExists('essentials_request');
    }
}
