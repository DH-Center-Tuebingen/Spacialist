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
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('user_groups', function (Blueprint $table) {
            $table->integer('user_id');
            $table->integer('group_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        Schema::create('access_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('accessible_id');
            $table->text('accessible_type');
            $table->text('type');
            $table->timestamps();
        });

        Schema::create('access_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('restrictable_id');
            $table->text('restrictable_type');
            $table->integer('guardable_id');
            $table->text('guardable_type');
            $table->text('rule_type');
            $table->jsonb('rule_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_rules');
        Schema::dropIfExists('access_types');
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('groups');
    }
};
