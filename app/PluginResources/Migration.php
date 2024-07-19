<?php

namespace App\PluginResources;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Migration extends Module {
    
    private function getPath() {
        $name = $this->getPlugin()->name;
        return base_path("app/Plugins/$name/Migration");
    }
    
    private function getMigrationPath($name){
        $path = $this->getPath();
        return base_path("$path/$name");
    }
    
    private function getSortedMigrations(bool $desc = false) : array {
        $migrationPath = $this->getPath();
        if(file_exists($migrationPath) && is_dir($migrationPath)) {
            $migrations = collect(File::files($migrationPath))->map(function($f) {
                return $f->getFilename();
            });
            if($desc) {
                $migrations = $migrations->sortDesc();
            } else {
                $migrations = $migrations->sort();
            }

            return $migrations->values()->toArray();
        }

        return [];
    }
    
    
    public function migrationGenerator(){
        foreach($this->getSortedMigrations() as $migration) {
            preg_match("/^[1-9]\d{3}_\d{2}_\d{2}_\d{6}_(.*)\.php$/", $migration, $matches);
            if(count($matches) != 2) continue;

            $className = Str::studly($matches[1]);
            require($this->getMigrationPath($migration));
            $prefixedClassName = $this->getPlugin()->getClassPath(['Migration', $className]);
            $instance = new $prefixedClassName();
            yield $instance;
        }
    }
    
    public function migrate() {
        foreach($this->migrationGenerator() as $instance) {
            call_user_func([$instance, 'migrate']);
        }        
    }

    public function rollback() {
        foreach($this->migrationGenerator() as $instance) {
            call_user_func([$instance, 'rollback']);
        }    
    }
}