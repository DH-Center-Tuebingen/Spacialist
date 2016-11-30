<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContextValuesThesaurusValForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add foreign key constraint to context_values->thesaurus_val
        Schema::table('context_values', function($table) {
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
        Schema::table('context_values', function($table){
            $table->dropForeign('context_values_thesaurus_val_foreign');
        });
    }
}
