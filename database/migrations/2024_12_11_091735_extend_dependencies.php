<?php

use App\EntityAttribute;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        activity()->disableLogging();

        $entityAttributes = EntityAttribute::whereNotNull('depends_on')->get();

        foreach($entityAttributes as $attribute) {
            $dependency = $attribute->depends_on;
            $dependsOn = array_keys($dependency)[0];
            $value = array_values($dependency)[0];
            $arrayDependency = [
                'union' => false,
                'groups' => [[
                    'union' => true,
                    'rules' => [
                        [
                            'value' => $value['value'],
                            'operator' => $value['operator'],
                            'on' => $dependsOn,
                        ],
                    ],
                ]],
            ];
            $attribute->depends_on = $arrayDependency;
            $attribute->saveQuietly();
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        $entityAttributes = EntityAttribute::whereNotNull('depends_on')->get();

        foreach($entityAttributes as $attribute) {
            $arrayDependency = $attribute->depends_on;
            $dependency = $arrayDependency['groups'][0]['rules'][0];
            $attribute->depends_on = [
                $dependency['on'] => [
                    'value' => $dependency['value'],
                    'operator' => $dependency['operator'],
                    'dependant' => $attribute->attribute->id,
                ],
            ];
            $attribute->saveQuietly();
        }

        activity()->enableLogging();
    }
};
