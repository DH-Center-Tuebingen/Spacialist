<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-tests {dir} {--type=unit} {--outdir=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a blank test file for every file of a specific subdirectory of the app directory';


    private function validDirectoryPath($path) {
        return !str_contains($path, "..");
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dir=$this->argument('dir');

        if($dir == ".") {
            $dir = "";
        }

        if(!$this->validDirectoryPath($dir)) {
            $this->error("Invalid dir: Going up in the directory structure is not allowed");
            return;
        }

        $outdir = $this->option('outdir');
        if(!$this->validDirectoryPath($outdir)) {
            $this->error("Invalid outdir: Going up in the directory structure is not allowed");
            return;
        }

        if(is_dir(app_path($dir))) {
            $files = scandir(app_path($dir));
            foreach($files as $file) {
                if($file == '.' || $file == '..') continue;
                if(is_dir(app_path($dir . DIRECTORY_SEPARATOR . $file))) continue;
                $this->generateTestFor(trim($file));
            }
        } else {
            $this->error("Directory $dir does not exist");
        }
    }

    private function generateTestFor($fileName) {
        $location = $this->option("type");
        $outdir = $this->option("outdir");

        if($outdir != "") {
            $location = implode(DIRECTORY_SEPARATOR, [$location, $outdir]);
        }

        $path = implode(DIRECTORY_SEPARATOR, [base_path('tests') , $location , str_replace(".php", "Test.php", $fileName)]);
        if(!file_exists($path)) {
            touch($path);
            $this->info("✔️  File created: $path");
        }else{
            $this->info("➡️  File already exists: $path");
        }
    }
}