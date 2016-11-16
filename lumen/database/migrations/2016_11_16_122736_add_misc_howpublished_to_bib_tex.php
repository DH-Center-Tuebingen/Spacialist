<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMiscHowpublishedToBibTex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bib_tex', function (Blueprint $table) {
            $table->text('misc');
            $table->text('howpublished');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bib_tex', function (Blueprint $table) {
            $table->dropColumn('howpublished');
            $table->dropColumn('misc');
        });
    }
}
