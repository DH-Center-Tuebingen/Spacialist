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
        Schema::create('plugin_service_scopes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('plugin_id')->constrained('plugins')->onDelete('cascade')->onUpdate('cascade');
            $table->string('on');
            $table->string('namespace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_service_scopes');
    }
};
