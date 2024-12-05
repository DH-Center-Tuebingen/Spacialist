<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToListContents;

return new class extends Migration
{
    private array $dirs = ['avatars', 'bibliography', 'plugins'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        activity()->disableLogging();

        $public = Storage::disk('public');
        $private = Storage::disk('local');
        foreach($this->dirs as $dir) {
            if($public->exists($dir)) {
                if(!$private->exists($dir)) {
                    $private->makeDirectory($dir);
                }
                try {
                    $files = $public->files($dir);
                    foreach($files as $file) {
                        $private->put($file, $public->get($file));
                        $public->delete($file);
                    }
                    $public->deleteDirectory($dir);
                } catch(UnableToListContents $symLinkException) {
                    // local plugin test env is the only case where we use sym links
                    // thus we have to catch it, but there is no easy Laravel/Flysystem
                    // approach to handle sym links
                }
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

        $public = Storage::disk('public');
        $private = Storage::disk('local');
        foreach($this->dirs as $dir) {
            if($private->exists($dir)) {
                if(!$public->exists($dir)) {
                    $public->makeDirectory($dir);
                }
                try {
                    $files = $private->files($dir);
                    foreach($files as $file) {
                        $public->put($file, $private->get($file));
                        $private->delete($file);
                    }
                    $private->deleteDirectory($dir);
                } catch(UnableToListContents $symLinkException) {
                    // local plugin test env is the only case where we use sym links
                    // thus we have to catch it, but there is no easy Laravel/Flysystem
                    // approach to handle sym links
                }
            }
        }

        activity()->enableLogging();
    }
};
