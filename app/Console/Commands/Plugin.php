<?php

namespace App\Console\Commands;

use App\Plugin as PluginModel;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;


class Plugin extends Command {
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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $validActions = ['install', 'uninstall', 'link'];

        $action = $this->argument('action');
        if(!in_array($action, $validActions)) {
            $this->error('Invalid action! Valid actions are: ' . implode(', ', $validActions));
            return 1;
        }

        try {
            $name = $this->argument('name');
            $this->line(ucfirst($action) . ' plugin ' . $name . '...');

            // When we link the plugin, it is not setup correctly,
            // so the Plugin::discoverPluginByName() will look for a 
            // non-existant 'info.xml' at the 'plugins/PLUGIN_NAME/App'
            // directory. So we need to handle this case separately. 
            if($action == 'link') {
                $this->link($name);
            }

            $plugin = PluginModel::discoverPluginByName($name);
            if(!$plugin) {
                $this->error('Plugin "' . $name . '" not found!');
                return 1;
            }

            switch($action) {
                case 'install':
                    $plugin->handleInstallation();
                    break;
                case 'uninstall':
                    $plugin->handleUninstall();
                    break;
                case 'link':
                    $this->symlinkJavaScriptFile($plugin);
                    break;
                default:
                    $this->error('Action not implemented: $action');
                    return 1;
            }
            $this->info("Action '$action' on plugin $name finished successfully!");
        } catch(\Exception $e) {
            $this->error("Could not perform action '$action' on plugin $name: " . $e->getMessage());
        }
    }

    // Create symlinks for all directories in the 'lib' directory of the plugin
    private function link($name) {
        $basePath = PluginModel::getPathFor($name . '/lib');

        if(!File::exists($basePath) || !File::isDirectory($basePath)) {
            throw new \Exception("Plugin directory '$name' not found at $basePath");
        }
        foreach(File::directories($basePath) as $dir) {
            $targetPath = PluginModel::getPathFor($name) . DIRECTORY_SEPARATOR  . basename($dir);
            $this->line($targetPath);

            // Clear the cache to ensure accurate results
            clearstatcache(true, $targetPath);

            // Check if the target path is a symbolic link
            if(is_link($targetPath)) {
                if(file_exists(readlink($targetPath))) {
                    $this->info("The target of the link exists: " . readlink($targetPath));
                    continue;
                } else {
                    $this->info("The target of the link does not exist. Removing stale link...");
                    unlink($targetPath); // Remove the stale symbolic link
                }
            }

            if(file_exists($targetPath)) {
                $this->info("Link already exists at $targetPath. Skipping...");
            } else {
                $this->info("Linking $dir to $targetPath");
                File::link($dir, $targetPath);
            }
        }
    }

    private function symlinkJavaScriptFile($plugin) {

        $jsDir = PluginModel::getPathFor('js');
        if(!file_exists($jsDir)) {
            mkdir($jsDir);
        }

        $this->ensureJavaScriptFile($plugin);
        $plugin->linkScript();
    }

    private function ensureJavaScriptFile($plugin) {
        $jsFile = $plugin->getPath('js/script.js');
        $this->info("Ensuring JavaScript file exists at ' $jsFile'...");
        if(!file_exists($jsFile)) {

            if(!file_exists($plugin->getPath('js'))) {
                $this->info("Creating 'js' directory...");
                $success = mkdir($plugin->getPath('js'));
                if(!$success) {
                    throw new \Exception("Could not create 'js' directory");
                } else {
                    $this->info("Created 'js' directory successfully!");
                }
            }

            $packageJson = $plugin->getPath('package.json');

            if(!file_exists($packageJson)) {
                throw new \Exception("package.json not found at $packageJson");
            }

            $package = json_decode(file_get_contents($packageJson), true);
            $packageName = $package["name"];
            if(!$packageName) {
                throw new \Exception("package.json does not contain a 'name' field");
            }

            $jsDiscSrc = $plugin->getPath("dist/$packageName.umd.js");
            if(!file_exists($jsDiscSrc)) {
                throw new \Exception("JavaScript file not found at $jsDiscSrc");
            }

            $linkSuccess = File::link($jsDiscSrc, $jsFile);

            if(!$linkSuccess) {
                throw new \Exception("Could not create symlink from $jsDiscSrc to $jsFile");
            } else {
                $this->info("Created symlink from $jsDiscSrc to $jsFile");
            }
        } else {
            $this->info("JavaScript file already exists at $jsFile. Skipping...");
        }
    }
}
