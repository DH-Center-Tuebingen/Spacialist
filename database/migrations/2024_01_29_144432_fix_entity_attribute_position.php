<?php

use App\EntityAttribute;
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

        $entityTypes = EntityType::all();

        foreach($entityTypes as $type) {
            $pos = 1;
            $typeAttrs = EntityAttribute::where('entity_type_id', $type->id)
                ->orderBy('position', 'asc')
                ->get();
            foreach($typeAttrs as $attr) {
                $attr->position = $pos;
                $attr->saveQuietly();
                $pos++;
            }
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
