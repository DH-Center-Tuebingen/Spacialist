<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MovePluginFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-plugin-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to move all plugin scripts and css files from the private storage disk to the public disc.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');

        $directories = ["plugins", "plugin_css"];
        
        foreach($directories as $dir){
            $this->info("Processing $dir ...");
            $errors = 0;
            if(!$private->directoryExists($dir)){
                $this->alert("⚠ Directory not found, skipping: $dir");
                continue;
            }

            $files = $private->allFiles($dir);
            foreach($files as $file){
                if($public->exists($file)) {
                    $this->warn("⚠ Already exists, skipping: $file");
                } else {
                    $contents = $private->get($file);
                    $success = $public->put($file, $contents);

                    if($success) {
                        $private->delete($file);
                        $this->info("✔ Moved: $file");
                    } else {
                        $errors ++;
                        $this->alert("❌ Failed to copy, kept source file: $file");
                    }
                }
            }
            if($errors == 0) {
                $private->deleteDirectory($dir);
            }
        }

    }
}
