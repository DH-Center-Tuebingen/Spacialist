<?php

use App\Reference;
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

        Schema::table('references', function (Blueprint $table) {
            $table->integer('attribute_id')->nullable()->change();
        });

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        Reference::whereNull('attribute_id')->delete();
        Schema::table('references', function (Blueprint $table) {
            $table->integer('attribute_id')->nullable(false)->change();
        });

        activity()->enableLogging();
    }
};