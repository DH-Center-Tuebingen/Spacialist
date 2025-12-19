<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates all typed attribute value tables using a consistent structure.
 * Each table stores values of a specific type with proper column types and indexes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createIntegerTable();
        $this->createDoubleTable();
        $this->createStringTable();
        $this->createBooleanTable();
        $this->createDateTable();
        $this->createEntityTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'attribute_values_entity',
            'attribute_values_date',
            'attribute_values_boolean',
            'attribute_values_string',
            'attribute_values_double',
            'attribute_values_integer',
        ];

        foreach($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * Create table for integer values.
     */
    protected function createIntegerTable(): void
    {
        Schema::create('attribute_values_integer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->bigInteger('value');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_integer', function (Blueprint $table) {
            $table->index('value');
        });
    }

    /**
     * Create table for double/float values.
     */
    protected function createDoubleTable(): void
    {
        Schema::create('attribute_values_double', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->double('value');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_double', function (Blueprint $table) {
            $table->index('value');
        });
    }

    /**
     * Create table for string/text values.
     */
    protected function createStringTable(): void
    {
        Schema::create('attribute_values_string', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->text('value');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_string', function (Blueprint $table) {
            $table->fullText('value');
        });
    }

    /**
     * Create table for boolean values.
     */
    protected function createBooleanTable(): void
    {
        Schema::create('attribute_values_boolean', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->boolean('value');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_boolean', function (Blueprint $table) {
            $table->index('value');
        });
    }

    /**
     * Create table for date values.
     */
    protected function createDateTable(): void
    {
        Schema::create('attribute_values_date', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->date('value');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_date', function (Blueprint $table) {
            $table->index('value');
        });
    }

    /**
     * Create table for entity reference values.
     */
    protected function createEntityTable(): void
    {
        Schema::create('attribute_values_entity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->foreignId('value')->constrained('entities')->onDelete('cascade');
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->unique(['entity_id', 'attribute_id']);
        });

        Schema::table('attribute_values_entity', function (Blueprint $table) {
            $table->index('value');
        });
    }
};
