<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThConceptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('th_concept', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concept_url', 256)->unique();
            $table->string('concept_scheme', 256);
            $table->string('lasteditor', 64);
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
        Schema::drop('th_concept');
    }
}
