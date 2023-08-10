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

        Schema::table('attributes', function (Blueprint $table) {
            $table->boolean('is_system')->default(false);
            $table->boolean('multiple')->default(false);

            $table->dropForeign('attributes_thesaurus_url_foreign');
        });
        Schema::table('entity_attributes', function (Blueprint $table) {
            $table->jsonb('metadata')->nullable();
        });

        $separator = new Attribute();
        $separator->thesaurus_url = '';
        $separator->datatype = 'system-separator';
        $separator->is_system = true;
        $separator->multiple = true;
        $separator->save();

        activity()->enableLogging();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        activity()->disableLogging();

        Attribute::where('is_system', true)->delete();

        Schema::table('entity_attributes', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('is_system');
            $table->dropColumn('multiple');

            $table->foreign('thesaurus_url')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });

        activity()->enableLogging();
    }
};
