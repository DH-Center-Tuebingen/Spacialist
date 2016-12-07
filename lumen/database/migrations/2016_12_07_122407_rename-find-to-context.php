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
            $table->foreign('context_id')->references('id')->on('contexts');

            $table->renameColumn('find_val', 'context_val');
            $table->foreign('context_val')->references('id')->on('contexts');

        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('find_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('contexts');
        });

        // rename former foreign keys context_id to context_type_id
        Schema::table('contexts', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
            $table->foreign('context_type_id')->references('id')->on('context_types');

            $table->renameColumn('root', 'root_context_id');
            $table->foreign('root_context_id')->references('id')->on('contexts');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
            $table->foreign('context_type_id')->references('id')->on('context_types');
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
            $table->foreign('find_id')->references('id')->on('finds');

            $table->renameColumn('context_val', 'find_val');
            $table->foreign('find_val')->references('id')->on('finds');

        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('context_id', 'find_id');
            $table->foreign('find_id')->references('id')->on('finds');
        });

        // rename former foreign keys context_id to context_type_id
        Schema::table('finds', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('context_types');

            $table->renameColumn('root_context_id', 'root');
            $table->foreign('root')->references('id')->on('finds');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
            $table->foreign('context_id')->references('id')->on('context_types');
        });
        //
    }
}
