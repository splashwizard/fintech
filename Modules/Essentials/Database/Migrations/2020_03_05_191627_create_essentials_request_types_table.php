<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEssentialsRequestTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('essentials_request_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('request_type');
            $table->integer('max_request_count')->nullable();
            $table->enum('request_count_interval', ['month', 'year'])->nullable();
            $table->integer('business_id')->index();
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
        Schema::dropIfExists('essentials_request_types');
    }
}
