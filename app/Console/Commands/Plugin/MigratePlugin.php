<?php

namespace App\Console\Commands\Plugin;


use Illuminate\Console\Command;

use App\Plugin;

class MigratePlugin extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-plugin {--N|name= : The name of the plugin to run the migrations} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cli tool to run the migrations of a specific plugin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $name = $this->option('name');
        
        if(!$name){
            $this->error('You need to provide a plugin name');
            return 1;
        }
        
        $plugin = Plugin::where("name", $name)->first();
        
        if(!$plugin) {
            $this->error("Plugin with name '{$name}' not found.");
            return 1;
        }else{
            $this->info("Running migrations for plugin '{$name}'...");
            $plugin->runMigrations();
            $this->info("Migrations for plugin '{$name}' ran successfully.");
        }
    }
}