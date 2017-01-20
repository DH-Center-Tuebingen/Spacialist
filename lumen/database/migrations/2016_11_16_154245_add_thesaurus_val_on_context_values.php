<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThesaurusValOnContextValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->dropForeign(['th_val']);
            $table->renameColumn('th_val', 'thesaurus_val');
            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->dropForeign(['thesaurus_val']);
            $table->renameColumn('thesaurus_val', 'th_val');
            $table->foreign('th_val')->references('concept_url')->on('th_concept');
        });
    }
}
