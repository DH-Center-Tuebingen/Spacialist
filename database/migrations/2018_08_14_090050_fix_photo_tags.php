<?php

use App\FileTag;
use App\ThConcept;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixPhotoTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fileTags = FileTag::all();
        Schema::dropIfExists('photo_tags');
        Schema::create('photo_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('photo_id');
            $table->integer('concept_id');
            $table->timestamps();

            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('cascade');
            $table->foreign('concept_id')->references('id')->on('th_concept')->onDelete('cascade');
        });
        foreach($fileTags as $ft) {
            $newFt = new FileTag();
            $conceptId = ThConcept::where('concept_url', $ft->concept_url)
                ->value('id');
            $newFt->id = $ft->id;
            $newFt->photo_id = $ft->photo_id;
            $newFt->concept_id = $conceptId;
            $newFt->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $fileTags = FileTag::all();
        Schema::dropIfExists('photo_tags');
        Schema::create('photo_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('photo_id');
            $table->text('concept_url');
            $table->timestamps();

            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('cascade');
            $table->foreign('concept_url')->references('concept_url')->on('th_concept')->onDelete('cascade');
        });
        foreach($fileTags as $ft) {
            $newFt = new FileTag();
            $conceptUrl = ThConcept::where('id', $ft->concept_id)
                ->value('concept_url');
            $newFt->id = $ft->id;
            $newFt->photo_id = $ft->photo_id;
            $newFt->concept_url = $conceptUrl;
            $newFt->save();
        }
    }
}
