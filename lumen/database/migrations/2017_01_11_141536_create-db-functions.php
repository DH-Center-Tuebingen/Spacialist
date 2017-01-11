<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbFunctions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->statement('CREATE OR REPLACE VIEW getLabelForId AS
            SELECT  lbl.label,
                    con.concept_url,
                    lng.short_name
            FROM th_concept_label lbl
                JOIN th_language lng ON lbl.language_id = lng.id
                JOIN th_concept con ON con.id = lbl.concept_id
            ORDER BY con.id, lbl.concept_label_type
        ');

        Schema::getConnection()->statement('CREATE OR REPLACE VIEW getLabelForTmpId AS
            SELECT  lbl.label,
                    lng.short_name,
                    lbl.concept_id
            FROM th_concept_label lbl
                    JOIN th_language lng ON lbl.language_id = lng.id
            ORDER BY lbl.concept_label_type
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::getConnection()->statement('DROP VIEW getLabelForTmpId');
        Schema::getConnection()->statement('DROP VIEW getLabelForId');
    }
}
