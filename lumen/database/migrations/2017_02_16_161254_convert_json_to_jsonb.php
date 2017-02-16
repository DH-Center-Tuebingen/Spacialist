<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertJsonToJsonb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attribute_values', function($table) {
            $table->dropColumn('json_val');
        });
        Schema::table('attribute_values', function($table) {
            $table->jsonb('json_val')->nullable();
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
            $table->dropColumn('json_val');
        });
        Schema::table('attribute_values', function($table) {
            $table->json('json_val')->nullable();
        });
    }
}
