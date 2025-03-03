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
        Schema::table('bibliography', function (Blueprint $table) {
            $table->renameColumn('type', 'entry_type');
            $table->renameColumn('subtype', 'type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bibliography', function (Blueprint $table) {
            $table->renameColumn('type', 'subtype');
            $table->renameColumn('entry_type', 'type');
        });
    }
};
