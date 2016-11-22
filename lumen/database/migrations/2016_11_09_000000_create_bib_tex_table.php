<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBibTexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bib_tex', function (Blueprint $table) {
            $table->increments('id');
            $table->text('author')->nullable();
            $table->text('editor')->nullable();
            $table->text('title');
            $table->text('journal')->nullable();
            $table->text('year')->nullable();
            $table->text('pages')->nullable();
            $table->text('volume')->nullable();
            $table->text('number')->nullable();
            $table->text('booktitle')->nullable();
            $table->text('publisher')->nullable();
            $table->text('address')->nullable();
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
        Schema::dropIfExists('bib_tex');
    }
}
