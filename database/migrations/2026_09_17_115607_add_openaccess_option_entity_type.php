<?php

use App\Attribute;
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

        Schema::table('entity_types', function (Blueprint $table) {
            $table->boolean('in_open_access')->default(true);
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

        Schema::table('entity_types', function (Blueprint $table) {
            $table->dropColumn('in_open_access');
            $table->dropColumn('metadata');
        });

        activity()->enableLogging();
    }
};
