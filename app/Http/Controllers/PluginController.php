<?php

namespace App\Http\Controllers;

use App\Plugin;
use App\Preference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

    public function loadScript(Request $request, $id, $name) {
        $emptyResponse = response()->json('');

        try {
            $plugin = Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            // $scriptName = $request->get('filename');
            $scriptPath = base_path("app/Plugins/$plugin->name/js/$name");
            if(!File::isFile($scriptPath)) {
                info("is not a file at $scriptPath");
                return $emptyResponse;
            }

            $xmlString = file_get_contents($scriptPath);
            info("FOUND!");
            info($xmlString);
            // $xmlObject = simplexml_load_string($xmlString);

            // return json_decode(json_encode($xmlObject), true);
            return response()->json($xmlString);

        } catch (ModelNotFoundException $e) {
            return $emptyResponse;
        }
    }

    public function getPlugins(Request $request) {
        $pluginPath = base_path('app/Plugins');
        $availablePlugins = File::directories($pluginPath);
        foreach($availablePlugins as $ap) {
            $info = Plugin::getInfo($ap);
            if($info !== false) {
                $id = $info['name'];
                $plugin = Plugin::where('name', $id)->first();
                if(!isset($plugin)) {
                    $plugin = new Plugin();
                    $plugin->name = $id;
                    $plugin->version = $info['version'];
                    $plugin->save();
                } else if($plugin->version != $info['version']) {
                    // installed version splitted
                    preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $plugin->version, $iv);
                    // available/latest version splitted
                    preg_match('/(\d+)\.(\d+).(\d+)(-.+)?/', $info['version'], $lv);

                    if(
                        ($lv[1] > $iv[1] || $lv[2] > $iv[2] || $lv[3] > $iv[3]) ||
                        (!isset($lv[4]) && isset($iv[4])) ||
                        (isset($lv[4]) && isset($iv[4]) && $lv[4] > $iv[4])
                    ) {
                        $plugin->update_available = $info['version'];
                    } else {
                        $plugin->update_available = null;
                    }
                    $plugin->save();
                }
            }
        }

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
        }

        return response()->json($plugins);
    }

    public function installPlugin(Request $request, $id) {
        try {
            Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            // Already installed
            return response()->json([], 204);
        } catch(ModelNotFoundException $e) {
            $plugin = Plugin::where('id', $id)->whereNull('installed_at')->first();
            $name = $plugin->name;
            $migrationPath = base_path("app/Plugins/$name/database/migrations");
            $migrations = File::files($migrationPath);
            foreach ($migrations as $migration) {
                $filename = $migration->getFilename();
                info($filename);
                Artisan::call('migrate', [
                    '--path' => "/app/Plugins/$name/database/migrations/$filename",
                    '--force' => true,
                ]);
            }
            $plugin->installed_at = Carbon::now();
            $plugin->save();
            return response()->json($plugin);
        }
    }

    public function uninstallPlugin(Request $request, $id) {
        try {
            $plugin = Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            $name = $plugin->name;
            $migrationPath = base_path("app/Plugins/$name/database/migrations");
            $migrations = File::files($migrationPath);
            // undo migrations in reversed order
            rsort($migrations);
            foreach($migrations as $migration) {
                $filename = $migration->getFilename();
                Artisan::call('migrate:rollback', [
                    '--path' => "/app/Plugins/$name/database/migrations/$filename",
                    '--force' => true,
                ]);
            }

            $plugin->installed_at = null;
            $plugin->save();
            return response()->json($plugin);
        } catch (ModelNotFoundException $e) {
            // Already uninstalled
            return response()->json([], 204);
        }
    }

    public function removePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            $name = $plugin->name;
            $migrationPath = base_path("app/Plugins/$name/database/migrations");
            $migrations = File::files($migrationPath);
            // undo migrations in reversed order
            rsort($migrations);
            foreach($migrations as $migration) {
                $filename = $migration->getFilename();
                Artisan::call('migrate:rollback', [
                    '--path' => "/app/Plugins/$name/database/migrations/$filename",
                    '--force' => true,
                ]);
            }
        } catch (ModelNotFoundException $e) {
            // Not installed
            $plugin = Plugin::where('id', $id)->firstOrFail();
            $name = $plugin->name;
        }

        sp_remove_dir(base_path("app/Plugins/$name"));

        $plugin->delete();
        return response()->json([], 204);
    }
}
