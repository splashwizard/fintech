<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('notice_id');
            $table->integer('lang_id')->default(1);
            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('desktop_image');
            $table->string('mobile_image');
            $table->text('content')->nullable();
            $table->integer('sequence');
            $table->enum('show', ['active', 'inactive']);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
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
        Schema::dropIfExists('notices');
    }
}
