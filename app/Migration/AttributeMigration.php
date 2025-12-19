<?php

namespace App\Migration;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class AttributeMigration extends Migration
{
    /**
     * Get the attribute type name (e.g., 'integer', 'string', 'double').
     * This will be used to name the table as '{type}_attribute_values'.
     */
    abstract protected function getAttributeType(): string;

    /**
     * Define the value column(s) for this attribute type.
     * This is where you specify the actual data storage column.
     * 
     * @param Blueprint $table
     * @return void
     */
    abstract protected function defineValueColumn(Blueprint $table): void;

    /**
     * Optionally define custom indexes for the value column(s).
     * By default, a standard index on 'value' is created.
     * Override this to add custom indexes (e.g., fulltext, composite).
     * 
     * @param Blueprint $table
     * @return void
     */
    protected function defineIndexes(Blueprint $table): void
    {
        // Default: Add index on 'value' column if it exists
        if(Schema::hasColumn($this->getTableName(), 'value')) {
            $table->index('value');
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->getTableName(), function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            
            // Define the value column(s) - specific to each attribute type
            $this->defineValueColumn($table);
            
            $table->integer('certainty')->nullable();
            $table->string('moderation_state')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            // Ensure one value per entity-attribute combination
            $table->unique(['entity_id', 'attribute_id']);
        });

        // Add indexes after table creation
        Schema::table($this->getTableName(), function (Blueprint $table) {
            $this->defineIndexes($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->getTableName());
    }

    /**
     * Get the full table name for this attribute type.
     */
    protected function getTableName(): string
    {
        return 'attribute_values_' . $this->getAttributeType();
    }
}
