<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ph_photo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 256);
            $table->timestamp('modified');
            $table->string('cameraname', 256);
            $table->integer('photographer_id')->unsigned();
            $table->timestamp('created');
            $table->string('thumb', 256)->default('');
            $table->integer('orientation')->default(1);
            $table->string('copyright', 512)->default('');
            $table->string('description', 1024)->default('');
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
        Schema::drop('ph_photo');
    }
}
