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
        Schema::create('plugin_service_access_points', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('identifier');
            $table->string('label');
            $table->timestamps();
            $table->foreignId('plugin_id')->references('id')->on('plugins')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_service_access_points');
    }
};
