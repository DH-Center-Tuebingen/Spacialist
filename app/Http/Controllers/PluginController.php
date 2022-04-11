<?php

namespace App\Http\Controllers;

use App\Plugin;
use App\Preference;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

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
        Plugin::discoverPlugins();

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
            try {
                $plugin->handleInstallation();
            } catch(ModelNotFoundException $e) {
                return response()->json([
                    'error' => __('Error while installing plugin. Preset does not exist.')
                ], 403);
            }

            return response()->json($plugin);
        }
    }

    public function updatePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::firstOrFail($id);
        } catch(ModelNotFoundException $e) {
            return response()->json([
                'error' => __('Error while installing plugin. Preset does not exist.')
            ], 403);
        }

        $plugin->handleUpdate();
        return response()->json([], 204);
    }

    public function uninstallPlugin(Request $request, $id) {
        try {
            $plugin = Plugin::where('id', $id)->whereNotNull('installed_at')->firstOrFail();
            $plugin->handleUninstall();
            return response()->json($plugin);
        } catch (ModelNotFoundException $e) {
            // Already uninstalled
            return response()->json([], 204);
        }
    }

    public function removePlugin(Request $request, $id) {
        try {
            $plugin = Plugin::firstOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => __('This plugin does not exist.')
            ], 403);
        }

        $plugin->handleRemove();
        $plugin->delete();
        return response()->json([], 204);
    }
}
