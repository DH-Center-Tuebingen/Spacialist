<?php

namespace App\Http\Controllers;

use App\Exceptions\PluginLifecycleException;
use App\Plugin;
use App\Preference;
use App\Services\PluginManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginUploader;
use App\Services\Plugin\AttributeService;
use App\Services\Plugin\CssService;
use App\Services\Plugin\MigrationService;
use App\Services\Plugin\DiscoveryService;
use App\Services\Plugin\ScriptService;
use App\Support\Log\PluginLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    private function requireInstalled(Plugin $plugin) {
        if(!isset($plugin->installed_at)) {
            abort(403, __('This plugin is not installed.'));
        }
    }

    private function requireNotInstalled(Plugin $plugin) {
        if(isset($plugin->installed_at)) {
            abort(403, __('This plugin is already installed.'));
        }
    }

    public function getPlugins(Request $request) {
        app(DiscoveryService::class)->discover();

        $plugins = [];
        if($request->query('installed') == 1) {
            $plugins = app(PluginManager::class)->getInstalledPlugins();
        } else if($request->query('uninstalled') == 1) {
            // We only cache installed plugins. When we need 
            $plugins = Plugin::whereNull('installed_at')->get();
        } else {
            $plugins = app(PluginManager::class)->getPlugins();
        }

        $attributesMap = app(AttributeService::class)->getMappedByPlugins();
        foreach($plugins as $plugin) {
            $plugin->registeredAttributes = $attributesMap[$plugin->id] ?? [];
        }

        return response()->json($plugins);
    }

    public function uploadPlugin(Request $request) {

        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $uploader = app(PluginUploader::class);
        $uploadResult = $uploader->upload($request->file('file'));

        $pluginName = $uploadResult->pluginName;

        if($uploadResult->isUpdate()) {
            $plugin = $uploadResult->plugin;
            $success = DB::transaction(function () use ($plugin) {
                app(PluginManager::class)->update($plugin);
                
                // Return true if update was successful.
                return true;
            });
        } else {
            $plugin = app(DiscoveryService::class)->discoverByName($pluginName);
        }

        if(!$success || !isset($plugin) ) {
            $uploader->restoreBackup($pluginName);
            PluginLog::forName($pluginName)->error("Plugin was uploaded but could not be read. Backup has been restored.");

            return response()->json([
                'error' => __('Plugin could not be initialized after upload. If it was an update attempt, the previous version has been restored.')
            ], 403);
        }

        return response()->json($plugin);
    }

    public function publishScript(Plugin $plugin) {
        $this->requireInstalled($plugin);
        $scriptUrl = app(ScriptService::class)->publish($plugin);
        return response()->json($scriptUrl);
    }

    public function installPlugin(Request $request, Plugin $plugin) {
        $this->requireNotInstalled($plugin);

        try{
            app(PluginManager::class)->install($plugin);
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('Error while installing plugin. Preset does not exist.')
            ], 403);
        }catch (PluginLifecycleException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }catch (\Exception $e) {
            report($e);
            PluginLog::for($plugin)->error("Unexpected error during plugin installation: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => __('Error while installing plugin. Please check file permissions or ask your system administrator.')
            ], 403);
        }

        return response()->json([
            'plugin' => $plugin,
            'scripts' => [app(ScriptService::class)->getUrl($plugin)],
            'styles' => app(CssService::class)->getUrls($plugin),
        ]);
    }

    public function getChangelog(Request $request, Plugin $plugin) {
        $changelog = PluginDirectory::fromPlugin($plugin)->readChangelog();
        return response()->json($changelog);
    }

    public function uninstallPlugin(Request $request, Plugin $plugin) {
        $this->requireInstalled($plugin);

        try{
            app(PluginManager::class)->uninstall($plugin);

            return response()->json([
                'plugin' => $plugin,
                'scripts' => [app(ScriptService::class)->getUrl($plugin)],
                'styles' => app(CssService::class)->getUrls($plugin),
            ]);
        }catch (ModelNotFoundException $e) {
            // Already uninstalled
            return response()->json([], 204);
        }
    }

    /**
     * Removes the plugin from the system 
     */
    public function removePlugin(Request $request, Plugin $plugin) {
        $this->requireNotInstalled($plugin);

        app(PluginManager::class)->remove($plugin);
        $plugin->delete();
        return response()->json([
            'scripts' => [app(ScriptService::class)->getUrl($plugin)],
            'styles' => app(CssService::class)->getUrls($plugin),
        ]);
    }


    /**
     * Downloads the plugin script from the server.
     * 
     * @param Request $request
     * @param string $filepath
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadScript(Request $request, string $filepath): JsonResponse|BinaryFileResponse {
        if($filepath === '') {
            return response()->json([
                'error' => __('No source provided.')
            ], 400);
        }
        return app(PluginManager::class)
            ->scriptService
            ->getStorageDirectory()
            ->downloadRelative($filepath);
    }

    /**
     * Downloads the css script from the server.
     * 
     * @param Request $request
     * @param string $filepath
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadCss(Request $request, string $filepath): JsonResponse|BinaryFileResponse {
        if($filepath === '') {
            return response()->json([
                'error' => __('No source provided.')
            ], 400);
        }
        return app(PluginManager::class)
            ->cssService
            ->getStorageDirectory()
            ->downloadRelative($filepath);
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
        $missingMigrations = app(MigrationService::class)->getMissingMigrations($plugin);
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
        app(DiscoveryService::class)->discover();
        app(PluginManager::class)->rebuildPluginCache();
        return response()->json(Plugin::all());
    }

    /**
     * Refreshes the metadata of a plugin.
     * @return \Illuminate\Http\JsonResponse - Returns the plugin with its metadata.
     */
    public function refreshInfo(Request $request, Plugin $plugin) {
        app(DiscoveryService::class)->discoverByName($plugin->name);
        app(PluginManager::class)->rebuildPluginCache();
        return response()->json($plugin);
    }
}
