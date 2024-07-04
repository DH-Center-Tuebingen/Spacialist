<?php

namespace App\Console\Commands;

use App\Plugin;
use App\User;
use App\Role;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


class LinkPlugin extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spacialist:plugin {name : Name of plugin to create link for} {action : Action to perform (install, uninstall)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cli tool to update the link of a plugin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $validActions = ['install', 'uninstall', 'link'];
        
        $action = $this->argument('action');
        if(!in_array($action, $validActions)){
            $this->error('Invalid action! Valid actions are: '.implode(', ', $validActions));
            return 1;
        }
        
        $name = $this->argument('name');
        $this->line(ucfirst($action) . ' plugin ' . $name.'...');
        
        
        // When we link the plugin, it is not setup correctly,
        // so the Plugin::discoverPluginByName() will look for a 
        // non-existant 'info.xml' at the 'plugins/PLUGIN_NAME/App'
        // directory. So we need to handle this case separately. 
        if($action !== 'link'){
            $plugin = Plugin::discoverPluginByName($name);
            if(!$plugin){
                $this->error('Plugin "'.$name.'" not found!');
                return 1;
            }
        }
        
        try{
            switch($action){
                case 'install':
                    $plugin->install();
                    break;
                case 'uninstall':
                    $plugin->handleUninstall();
                    break;
                case 'link':
                    $this->link($name);
                    break;
                default:
                    $this->error('Action not implemented: $action');
                    return 1;
            }
            $this->info("Action '$action' on plugin $name finished successfully!");
        } catch(\Exception $e){
            $this->error("Could not perform action '$action' on plugin $name: " . $e->getMessage());
        }
    }
    
    // Create symlinks for all directories in the 'lib' directory of the plugin
    private function link($name){
        $basePath = Plugin::getPathFor($name. '/lib');
        
        if(!File::exists($basePath) || !File::isDirectory($basePath)){
           throw new \Exception("Plugin directory '$name' not found at $basePath");
        }
        foreach(File::directories($basePath) as $dir){
            $targetPath = Plugin::getPathFor($name) . DIRECTORY_SEPARATOR  . basename($dir);
            $this->line($targetPath);
            
            // Clear the cache to ensure accurate results
            clearstatcache(true, $targetPath);
         
            // Check if the target path is a symbolic link
            if(is_link($targetPath)){                                
                if(file_exists(readlink($targetPath))){
                    $this->info("The target of the link exists: " . readlink($targetPath));
                    continue;
                } else {
                    $this->info("The target of the link does not exist. Removing stale link...");
                    unlink($targetPath); // Remove the stale symbolic link
                }
            }
            
            if(file_exists($targetPath)){
                $this->info("Link already exists at $targetPath. Skipping...");
            }else{
                $this->info("Linking $dir to $targetPath");
                File::link($dir, $targetPath);
            }
        }
    }
}