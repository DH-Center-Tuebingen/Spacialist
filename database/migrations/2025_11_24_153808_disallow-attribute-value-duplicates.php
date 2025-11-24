<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove duplicates and keep the row with the largest ID (newest)
        DB::statement("
            DELETE FROM attribute_values a
            USING attribute_values b
            WHERE a.id < b.id
              AND a.entity_id = b.entity_id
              AND a.attribute_id = b.attribute_id
        ");

        // 2. Add the unique constraint
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->unique(['entity_id', 'attribute_id'], 'entity_attribute_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove constraint on rollback
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropUnique('entity_attribute_unique');
        });
    }
};
