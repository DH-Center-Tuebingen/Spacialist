<?php

namespace App\Http\Controllers;

use App\Plugin;
use App\Preference;
use App\PluginMigration;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Str;

class PluginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        if(!Preference::hasPublicAccess()) {
            $this->middleware('auth')->except(['welcome', 'index']);
        }
        $this->middleware('guest')->only('welcome');
    }

    public function getPlugins(Request $request) {
        Plugin::updateState();

        $plugins = [];
        if($request->query('installed') == 1) {
            $plugins = Plugin::whereNotNull('installed_at')->get();
        } else if($request->query('uninstalled') == 1) {
            $plugins = Plugin::whereNull('installed_at')->get();
        } else {
            $plugins = Plugin::all();
        }

        foreach($plugins as $plugin) {
            $plugin->metadata = $plugin->getMetadata();
            $plugin->changelog = $plugin->getChangelog();
            $plugin->registeredAttributes = $plugin->getRegisteredAttributes();
        }

        return response()->json($plugins);
    }

    public function uploadPlugin(Request $request) {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $file = $request->file('file');

        $mandatoryFiles = [
            'App/info.xml',
            'js/script.js',
            'routes/api.php',
        ];

        $zipFile = new ZipArchive;
        $isOpen = $zipFile->open($file->getRealPath(), ZipArchive::RDONLY);

        if(!$isOpen) {
            return response()->json([
                'error' => __('Could not open provided plugin zip file. Aborting.')
            ], 403);
        }

        $rootFolder = $zipFile->getNameIndex(0);
        if(!Str::endsWith($rootFolder, '/')) {
            return response()->json([
                'error' => __('Format mismatch. Only a folder is allowed on root level.')
            ], 403);
        }
        $pluginName = substr($rootFolder, 0, -1);
        foreach($mandatoryFiles as $filepath) {
            if($zipFile->locateName("{$rootFolder}{$filepath}") === false) {
                return response()->json([
                    'error' => __("Format mismatch. Mandatory file '{$rootFolder}{$filepath}' is missing.")
                ], 403);
            }
        }

        $pluginPath = Plugin::getPluginPath($pluginName);
        if(file_exists($pluginPath)) {
            $installedPlugin = Plugin::where('name', $pluginName)->first();
            $infoContent = Plugin::getPluginInfo($zipFile->getFromName("{$rootFolder}App/info.xml"), true);

            if($installedPlugin->version >= $infoContent['version']) {
                return response()->json([
                    'error' => __("A plugin with the name '$pluginName' and the same or later version ({$infoContent['version']} and {$installedPlugin->version}) already exists. Aborting.")
                ], 403);
            }
        }

        $extractPath = Str::finish(Plugin::getPluginPath($pluginName), '/');
        $extracted = $zipFile->extractTo($extractPath);
        $zipFile->close();

        if(!$extracted) {
            return response()->json([
                'error' => __("Error while extracting zip file. Please check file permissions or ask your system adminstrator.")
            ], 403);
        }

        $plugin = Plugin::discoverPluginByName($pluginName);

        if(!isset($plugin)) {
            return response()->json([
                'error' => __("Error while reading from extracted content. Please check file permissions or ask your system adminstrator.")
            ], 403);
        }

        $plugin->metadata = $plugin->getMetadata();
        return response()->json($plugin);
    }

    public function installPlugin(Request $request, $id) {
        try {
            Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            // Already installed
            return response()->json([], 204);
        } catch(ModelNotFoundException $e) {            
            $plugin = Plugin::where('id', $id)->whereNull('installed_at')->first();
            try {
                $plugin->handleInstallation();
            } catch(ModelNotFoundException $e) {
                info("odelNotFoundException: " . $e->getMessage());
                return response()->json([
                    'error' => __('Error while installing plugin. Preset does not exist.')
                ], 403);
            } catch(\Exception $e) {
                info("Exception: " . $e->getMessage());
                return response()->json([
                    'error' => __('Error while installing plugin. Please check file permissions or ask your system administrator.')
                ], 403);
            }

            return response()->json([
                'plugin' => $plugin,
                'install_location' => $plugin->publicName(false),
            ]);
        }
    }

    public function updatePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This plugin does not exist.')
            ], 403);
        }
        try {
            $updatedFrom = $plugin->handleUpdate();
        } catch(\Exception $e) {
            return response()->json([
                'error' => __('Error while updating plugin. Please check file permissions or ask your system administrator.')
            ], 403);
        }
        $plugin->changelog = $plugin->getChangelog($updatedFrom);
        return response()->json($plugin);
    }

    public function uninstallPlugin(Request $request, $id) {
        try {
            $plugin = Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            $plugin->handleUninstall();
            return response()->json([
                'plugin' => $plugin,
                'uninstall_location' => $plugin->publicName(false),
            ]);
        } catch(ModelNotFoundException $e) {
            // Already uninstalled
            return response()->json([], 204);
        }
    }

    public function removePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This plugin does not exist.')
            ], 403);
        }

        $plugin->handleRemove();
        $plugin->delete();
        return response()->json([
            'uninstall_location' => $plugin->publicName(false),
        ]);
    }

    public function downloadScript(Request $request, string $filepath) {
        if($filepath === ''){
            return response()->json([
                'error' => __('No source provided.')
            ], 400);
        }
        return Plugin::getDirectory()->downloadRelative($filepath);
    }

    public function migrate(Request $request, Plugin $plugin) {
        $plugin->runMigrations();
        return response()->json($plugin);
    }

    public function rollback(Request $request, Plugin $plugin) {
        $plugin->rollbackMigrations();
        return response()->json($plugin);
    }

    public function addMigrationToDatabase(Request $request, Plugin $plugin) {
        $this->validate($request, [
            'name' => 'required|string'
        ]);
        $migrationName = $request->input('name');
        $missingMigrations = PluginMigration::getMissingMigrations($plugin);
        if(!in_array($migrationName, $missingMigrations)) {
            return response()->json([
                'error' => __('This migration does not exist or has already been run.')
            ], 400);
        }
        //Determine the next batch number
        $nextBatch = PluginMigration::max('batch') + 1;
        //Record the migration as run   
        PluginMigration::create([
            'plugin_id' => $plugin->id,
            'migration' => $migrationName,
            'batch' => $nextBatch
        ]);
        return response()->json($plugin->getMigrationState());
    }

     /**
     * Get the migration state for a plugin.
     */
    public function getMigrationState(Request $request, Plugin $plugin) {
        return response()->json($plugin->getMigrationState());
    }

    //// REBASING:: Plugin Attribute
    // public function downloadScript(Request $request) {
    //     $file = "plugins/" . $request->query('src');
    //     return Plugin::getDirectory()->download($file);
    // }
}
