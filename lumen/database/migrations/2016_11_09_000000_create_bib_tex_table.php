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
            $table->text('author');
            $table->text('editor');
            $table->text('title');
            $table->text('journal');
            $table->text('year');
            $table->text('pages');
            $table->text('volume');
            $table->text('number');
            $table->text('booktitle');
            $table->text('publisher');
            $table->text('address');
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
