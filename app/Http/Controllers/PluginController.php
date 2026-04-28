<?php

namespace App\Http\Controllers;

use App\Plugin;
use App\Preference;
use App\Models\Plugin\Migration as PluginMigration;
use App\Services\Plugin\MigrationService;
use App\Services\PluginManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Str;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;

class PluginController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
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
            // $plugin->registeredAttributes = $plugin->getRegisteredAttributes();
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

        $pluginPath = PluginDirectory::getPathByName($pluginName);
        if(file_exists($pluginPath)) {
            $installedPlugin = Plugin::where('name', $pluginName)->first();
            $manifest = PluginManifest::parse($zipFile->getFromName("{$rootFolder}App/info.xml"));
    
            $existingVersion = $installedPlugin->version ?? '0.0.0';
            $uploadedVersion = $manifest->getVersion();
            
            if(version_compare($existingVersion, $uploadedVersion, ">=")) {
                return response()->json([
                    'error' => __("A plugin with the name ':pluginName' and the same or later version (:uploadedVersion and :existingVersion) already exists. Aborting.", [
                        'pluginName' => $pluginName,
                        'uploadedVersion' => $uploadedVersion,
                        'existingVersion' => $existingVersion,
                    ])
                ], 403);
            }
        }

        $extractPath = Str::finish($pluginPath, '/');
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

    public function publishScript(Plugin $plugin) {
        if(!isset($plugin->installed_at)) {
            return response()->json([
                'error' => __('This plugin is not installed.'),
            ], 403);
        }

        $scriptUrl = app(PluginManager::class)->scriptService->publish($plugin);
        return response()->json($scriptUrl);
    }

    public function installPlugin(Request $request, $id) {
        try {
            Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            // Already installed
            return response()->json([], 204);
        } catch(ModelNotFoundException $e) {
            $plugin = Plugin::where('id', $id)->whereNull('installed_at')->first();
            try {
                app(\App\Services\PluginManager::class)->install($plugin);
            } catch(ModelNotFoundException $e) {
                info("ModelNotFoundException: " . $e->getMessage());
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
                'scripts' => [app(PluginManager::class)->scriptService->getUrl($plugin)],
                'styles' => app(PluginManager::class)->cssService->getUrls($plugin),
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
            $updatedFrom = app(PluginManager::class)->update($plugin);
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
            app(PluginManager::class)->uninstall($plugin);
            
            return response()->json([
                'plugin' => $plugin,
                'scripts' => [app(PluginManager::class)->scriptService->getUrl($plugin)],
                'styles' => app(PluginManager::class)->cssService->getUrls($plugin),
            ]);
        } catch(ModelNotFoundException $e) {
            // Already uninstalled
            return response()->json([], 204);
        }
    }

    /**
     * Removes the plugin from the system 
     */
    public function removePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::findOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This plugin does not exist.')
            ], 403);
        }

        app(PluginManager::class)->remove($plugin);
        $plugin->delete();
        return response()->json([
                'scripts' => [app(PluginManager::class)->scriptService->getUrl($plugin)],
                'styles' => app(PluginManager::class)->cssService->getUrls($plugin),
        ]);
    }

    /**
     * Downloads the plugin script from the directory.
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse - Returns the file as BinaryFileResponse or Response if the file is not inside the directory.
     */
    public function downloadScript(Request $request, string $filepath) {
        if($filepath === '') {
            return response()->json([
                'error' => __('No source provided.')
            ], 400);
        }
        return app(PluginManager::class)
            ->scriptService
            ->downloadDirectory()
            ->downloadRelative($filepath);
    }

    public function downloadCss(Request $request, string $filepath) {
        if($filepath === '') {
            return response()->json([
                'error' => __('No source provided.')
            ], 400);
        }
        return app(PluginManager::class)->cssService->getStorageDirectory()->downloadRelative($filepath);
    }

    /** 
     * Runs all missing migrations of a plugin.
     * @return \Illuminate\Http\JsonResponse - Returns the current migration state after execution
     */
    public function migrate(Request $request, Plugin $plugin) {
        app(MigrationService::class)->run($plugin);
        $migrationState = app(MigrationService::class)->inspect($plugin);
        return response()->json($migrationState);
    }

    /**
     * Rolls back all applied migrations of a plugin.
     * @return \Illuminate\Http\JsonResponse - Returns the current migration state after execution
     */
    public function rollback(Request $request, Plugin $plugin) {
        app(MigrationService::class)->rollback($plugin);
        $migrationState = app(MigrationService::class)->inspect($plugin);
        return response()->json($migrationState);
    }

    /**
     * Adds a migration to the database without running it.
     * This is primarily used if the plugin was installed before the migration system was implemented.
     * @return \Illuminate\Http\JsonResponse - Returns the current migration state after execution
     */
    public function addMigrationToDatabase(Request $request, Plugin $plugin) {
        $this->validate($request, [
            'name' => 'required|string'
        ]);

        $migrationName = $request->input('name');
        $missingMigrations = app(PluginManager::class)->migrationService->getMissingMigrations($plugin);
        if(!in_array($migrationName, $missingMigrations)) {
            return response()->json([
                'error' => __('This migration does not exist or has already been run.')
            ], 400);
        }

        app(MigrationService::class)->set($migrationName, $plugin);
        $migrationState = app(MigrationService::class)->inspect($plugin);
        return response()->json($migrationState);
    }

    /**
     * Get the migration state for a plugin.
     * @return \Illuminate\Http\JsonResponse - Returns the current migration state after execution
     */
    public function getMigrationState(Request $request, Plugin $plugin) {
        $migrationState = app(MigrationService::class)->inspect($plugin);
        return response()->json($migrationState);
    }

    /**
     * Rebuilds the plugin cache and returns all plugins with their metadata.
     * @return \Illuminate\Http\JsonResponse - Returns all plugins with their metadata after rebuilding the cache.
     */
    public function refresh(Request $request) {
        app(PluginManager::class)->rebuildPluginCache();
        return response()->json(Plugin::getWithMetadata());
    }

    /**
     * Refreshes the metadata of a plugin.
     * @return \Illuminate\Http\JsonResponse - Returns the plugin with its metadata.
     */
    public function refreshInfo(Request $request, Plugin $plugin) {
        $plugin->metadata = $plugin->getMetadata(true);
        return response()->json($plugin);
    }
}
