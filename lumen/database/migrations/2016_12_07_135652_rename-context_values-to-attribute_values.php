<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameContextValuesToAttributeValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('context_values', function($table) {
            $table->dropForeign(['context_id']);
            $table->dropForeign(['attribute_id']);
            $table->dropForeign(['context_val']);
        });
        Schema::rename('context_values', 'attribute_values');
        Schema::table('attribute_values', function($table) {
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('context_val')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attribute_values', function($table) {
            $table->dropForeign(['context_id']);
            $table->dropForeign(['attribute_id']);
            $table->dropForeign(['context_val']);
        });
        Schema::rename('attribute_values', 'context_values');
        Schema::table('context_values', function($table) {
            $table->foreign('context_id')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('context_val')->references('id')->on('contexts')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
        });        //
    }
}
