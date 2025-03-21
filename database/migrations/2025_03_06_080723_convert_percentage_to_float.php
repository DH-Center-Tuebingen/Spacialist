<?php

use App\Attribute;
use App\AttributeValue;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        activity()->disableLogging();

        $percentageAttributeValues = AttributeValue::whereHas('attribute', function ($query) {
            $query->where('datatype', 'percentage');
        })->get();
        foreach($percentageAttributeValues as $value) {
            $value->dbl_val = $value->int_val;
            $value->int_val = null;
            $value->saveQuietly();
        }

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        activity()->disableLogging();

        $percentageAttributeValues = AttributeValue::whereHas('attribute', function ($query) {
            $query->where('datatype', 'percentage');
        })->get();
        foreach($percentageAttributeValues as $value) {
            $value->int_val = intval($value->dbl_val);
            $value->dbl_val = null;
            $value->saveQuietly();
        }

        activity()->enableLogging();
    }
};
