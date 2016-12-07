<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameFindToContext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // drop constraints
        Schema::table('context_values', function ($table) {
            $table->dropForeign(['find_id']);
            $table->dropForeign(['find_val']);
            $table->dropForeign(['attribute_id']);
            $table->dropForeign(['thesaurus_val']);
        });
        Schema::table('sources', function ($table) {
            $table->dropForeign(['find_id']);
        });
        Schema::table('finds', function ($table) {
            $table->dropForeign(['context_id']);
            $table->dropForeign(['root']);
        });
        Schema::table('context_attributes', function ($table) {
            $table->dropForeign(['context_id']);
        });

        // rename table finds to contexts
        Schema::rename('finds', 'contexts');

        // rename all foreign keys and recreate constraints

        Schema::table('context_values', function ($table) {
            $table->renameColumn('find_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');

            $table->renameColumn('find_val', 'context_val');
            $table->foreign('context_val')->references('id')->on('contexts')->onDelete('cascade');

            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept');
        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('find_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
        });

        // rename former foreign keys context_id to context_type_id
        Schema::table('contexts', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
            $table->foreign('context_type_id')->references('id')->on('context_types')->onDelete('cascade');

            $table->renameColumn('root', 'root_context_id');
            $table->foreign('root_context_id')->references('id')->on('contexts')->onDelete('cascade');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
            $table->foreign('context_type_id')->references('id')->on('context_types')->onDelete('cascade');
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
        // drop constraints
        Schema::table('context_values', function ($table) {
            $table->dropForeign(['context_id']);
            $table->dropForeign(['context_val']);
            $table->dropForeign(['attribute_id']);
            $table->dropForeign(['thesaurus_val']);
        });
        Schema::table('sources', function ($table) {
            $table->dropForeign(['context_id']);
        });
        Schema::table('contexts', function ($table) {
            $table->dropForeign(['context_type_id']);
            $table->dropForeign(['root_context_id']);
        });
        Schema::table('context_attributes', function ($table) {
            $table->dropForeign(['context_type_id']);
        });

        // rename table finds to contexts
        Schema::rename('contexts', 'finds');

        // rename all foreign keys and recreate constraints

        Schema::table('context_values', function ($table) {
            $table->renameColumn('context_id', 'find_id');
            $table->foreign('find_id')->references('id')->on('finds')->onDelete('cascade');

            $table->renameColumn('context_val', 'find_val');
            $table->foreign('find_val')->references('id')->on('finds')->onDelete('cascade');

            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');

            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept');

        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('context_id', 'find_id');
            $table->foreign('find_id')->references('id')->on('finds')->onDelete('cascade');
        });

        // rename former foreign keys context_id to context_type_id
        Schema::table('finds', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('context_types')->onDelete('cascade');

            $table->renameColumn('root_context_id', 'root');
            $table->foreign('root')->references('id')->on('finds')->onDelete('cascade');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('context_types');
        });
        //
    }
}
