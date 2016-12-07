<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameFkConceptIdToConceptUrl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attributes', function ($table) {

            $table->dropForeign(['thesaurus_id']);
            $table->renameColumn('thesaurus_id', 'thesaurus_url');
            $table->foreign('thesaurus_url')->references('concept_url')->on('th_concept')->onDelete('cascade');

            $table->dropForeign(['thesaurus_root_id']);
            $table->renameColumn('thesaurus_root_id', 'thesaurus_root_url');
            $table->foreign('thesaurus_root_url')->references('concept_url')->on('th_concept');

        });
        Schema::table('context_types', function ($table) {
            $table->dropForeign(['thesaurus_id']);
            $table->renameColumn('thesaurus_id', 'thesaurus_url');
            $table->foreign('thesaurus_url')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attributes', function ($table) {

            $table->dropForeign(['thesaurus_url']);
            $table->renameColumn('thesaurus_url', 'thesaurus_id');
            $table->foreign('thesaurus_id')->references('concept_url')->on('th_concept')->onDelete('cascade');

            $table->dropForeign(['thesaurus_root_url']);
            $table->renameColumn('thesaurus_root_url', 'thesaurus_root_id');
            $table->foreign('thesaurus_root_id')->references('concept_url')->on('th_concept');

        });
        Schema::table('context_types', function ($table) {
            $table->dropForeign(['thesaurus_url']);
            $table->renameColumn('thesaurus_url', 'thesaurus_id');
            $table->foreign('thesaurus_id')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });
    }
}
