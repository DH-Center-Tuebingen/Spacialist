<?php

use App\AvailableLayer;
use App\EntityType;
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
            $table->text('color')->nullable();
        });

        // migrate existing colors from map plugin table
        if(Schema::hasTable('available_layers')) {
            $entityTypeLayers = AvailableLayer::has('entity_type')->get();

            foreach($entityTypeLayers as $entityTypeLayer) {
                $color = $entityTypeLayer->color;
                $entityTypeLayer->color = null;
                $entityTypeLayer->saveQuietly();
                $entityType = EntityType::find($entityTypeLayer->entity_type_id);
                $entityType->color = $color;
                $entityType->saveQuietly();
            }
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        if(Schema::hasTable('available_layers')) {
            $entityTypeLayers = AvailableLayer::has('entity_type')->get();

            foreach($entityTypeLayers as $entityTypeLayer) {
                $entityTypeLayer->color = $entityTypeLayer->entity_type->color;
                $entityTypeLayer->saveQuietly();
            }
        }

        Schema::table('entity_types', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        activity()->enableLogging();
    }
};
