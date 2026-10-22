<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->dropForeign(['thesaurus_val']);

            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->dropForeign(['thesaurus_val']);

            $table->foreign('thesaurus_val')->references('concept_url')->on('th_concept');
        });
    }
};
