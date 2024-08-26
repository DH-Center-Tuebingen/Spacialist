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
        activity()->disableLogging();

        Schema::table('attributes', function (Blueprint $table) {
            $table->jsonb('metadata')->nullable();
        });

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        activity()->enableLogging();
    }
};