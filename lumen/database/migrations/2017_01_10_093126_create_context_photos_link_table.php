<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContextPhotosLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('context_photos', function (Blueprint $table) {
            $table->integer('photo_id');
            $table->integer('context_id');
            $table->text('lasteditor')->default('console_user');

            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('cascade');            
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('context_photos');
    }
}
