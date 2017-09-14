<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use App\File;

class ConvertPhotoToFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->text('mime_type')->nullable();
            $table->text('cameraname')->nullable()->change();
            $table->integer('photographer_id')->nullable()->change();
            $table->text('thumb')->nullable()->change();
            $table->integer('orientation')->nullable()->change();
            $table->text('copyright')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        $photos = File::all();
        foreach($photos as $p) {
            $url = 'images/' . $p->name;
            $p->mime_type = Storage::mimeType($url);
            $p->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('mime_type');
        });
    }
}
