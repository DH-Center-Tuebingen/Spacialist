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

        Schema::table('bibliography', function (Blueprint $table) {
            $table->text('title')->nullable()->change();
        });

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        Schema::table('bibliography', function (Blueprint $table) {
            $table->text('title')->nullable(false)->change();
        });

        activity()->enableLogging();
    }
};
