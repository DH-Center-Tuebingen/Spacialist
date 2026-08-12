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

    public function uploadPlugin(Request $request) {

        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $uploader = app(PluginUploader::class);
        $uploadResult = $uploader->upload($request->file('file'));

        $pluginName = $uploadResult->pluginName;

        $fromVersion = null;
        $success = true;
        if($uploadResult->isUpdate()) {
            $plugin = $uploadResult->plugin;
            $fromVersion = $plugin->version;
            $success = DB::transaction(function () use ($plugin) {
                app(PluginManager::class)->update($plugin);
                // Return true if update was successful.
                return true;
            });
        } else {
            info("Plugin {$pluginName} was uploaded and installed successfully.");
            $plugin = app(DiscoveryService::class)->discoverByName($pluginName);
            if(!isset($plugin)) {
                $success = false;
            }
        }
        
        if(!$success) {
            $message = "";
            if($uploadResult->isUpdate()) {
                $uploader->restoreBackup($pluginName);
                $message = "Plugin was uploaded but could not be updated. Backup has been restored.";
                PluginLog::forName($pluginName)->error($message);
            } else {
                (new PluginDirectory($pluginName))->remove();
                $message = "Plugin upload was unsuccessful. Plugin directory has been removed.";
                PluginLog::forName($pluginName)->error($message);
            }

            return response()->json([
                'error' => __($message)
            ], 403);
        }

        return response()->json([
            "plugin" => $plugin,
            "scripts" => [app(ScriptService::class)->getUrl($plugin)],
            "styles" => app(CssService::class)->getUrls($plugin),
            "updated" => $uploadResult->isUpdate(),
            "fromVersion" => $fromVersion,
        ]);
    }

    /**
     * Installs the plugin.
     * 
     * @param Request $request
     * @param Plugin $plugin
     * @return JsonResponse<array{
     *     plugin: Plugin,
     *     scripts: string[],
     *     styles: string[]
     * }>
     */
    public function installPlugin(Request $request, Plugin $plugin): JsonResponse {
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

    /**
     * Uninstall the plugin. 
     * 
     * @param Request $request
     * @param Plugin $plugin
     * @return JsonResponse<array{
     *     plugin: Plugin,
     *     scripts: string[],
     *     styles: string[]
     * }> | JsonResponse<array>[] - Returns the plugin with its scripts and styles. If the plugin was already uninstalled, a 204 No Content response will be returned.
     */
    public function uninstallPlugin(Request $request, Plugin $plugin): JsonResponse {
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
     * 
     * @param Request $request
     * @param Plugin $plugin
     * @return JsonResponse<array{
     *     plugin: Plugin,
     *     scripts: string[],
     *     styles: string[]
     * }>
     */
    public function removePlugin(Request $request, Plugin $plugin): JsonResponse {
        $this->requireNotInstalled($plugin);

        app(PluginManager::class)->remove($plugin);
        $plugin->delete();
        return response()->json([
            'plugin' => $plugin,
            'scripts' => [app(ScriptService::class)->getUrl($plugin)],
            'styles' => app(CssService::class)->getUrls($plugin),
        ]);
    }
    
    /**
     * Gets all plugins. 
     * Can be limited to 'installed' and 'uninstalled' plugins by setting the respective query parameter to 1.
     * @param Request $request
     * @return JsonResponse<Plugin[]> - Returns the list of all plugins.
     */
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
    
    /**
     * Publishes the JavaScript file to the storage. 
     * 
     * @param Plugin $plugin
     * @return JsonResponse<string> - Returns the URL of the published script.
     */
    public function publishScript(Plugin $plugin) {
        $this->requireInstalled($plugin);
        $scriptUrl = app(ScriptService::class)->publish($plugin);
        return response()->json($scriptUrl);
    }

    /**
     * Get's the changelog of the plugin as md string.
     * If no changelog was found, an empty string will be returned 
     * 
     * @param Request $request
     * @param Plugin $plugin
     * @return JsonResponse<string> - Returns the plugin's changelog, or an empty string if no changelog was found.
     */
    public function getChangelog(Request $request, Plugin $plugin): JsonResponse {
        $changelog = PluginDirectory::fromPlugin($plugin)->readChangelog();
        return response()->json($changelog);
    }

    /**
     * Downloads the plugin script from the server.
     * 
     * @param Request $request
     * @param string $filepath
     * @return BinaryFileResponse|JsonResponse<string>
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
     * @return BinaryFileResponse|JsonResponse<string>
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
        $refreshedPlugin = app(DiscoveryService::class)->discoverByName($plugin->name);
        app(PluginManager::class)->rebuildPluginCache();
        return response()->json($refreshedPlugin);
    }
}
