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
        Schema::table('context_values', function ($table) {
            $table->renameColumn('find_id', 'context_id');
            $table->renameColumn('find_val', 'context_val');
        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('find_id', 'context_id');
        });
        Schema::rename('finds', 'contexts');
        Schema::table('contexts', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_id', 'context_type_id');
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
        Schema::table('context_values', function ($table) {
            $table->renameColumn('context_id', 'find_id');
            $table->renameColumn('context_val', 'find_val');
        });
        Schema::table('sources', function ($table) {
            $table->renameColumn('context_id', 'find_id');
        });
        Schema::rename('contexts', 'finds');
        Schema::table('finds', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
        });
        Schema::table('context_attributes', function ($table) {
            $table->renameColumn('context_type_id', 'context_id');
        });
        //
    }
}
