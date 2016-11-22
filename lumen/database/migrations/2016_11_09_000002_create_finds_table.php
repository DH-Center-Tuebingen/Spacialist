<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finds', function (Blueprint $table) {
            $table->increments('id');
			$table->string('name', 128);
			$table->integer('context_id')->unsigned();
			$table->float('lat', '8', '5')->nullable();
			$table->float('lng', '8', '5')->nullable();
			$table->integer('root')->nullable();
            $table->timestamps();

			$table->foreign('context_id')->references('id')->on('context_types')->onDelete('cascade');
			$table->foreign('root')->references('id')->on('finds')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('finds');
    }
}
