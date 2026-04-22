<?php
namespace App\Services\Plugin;

use App\Models\Plugin\Hook;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Method;
use App\Validators\HookValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HookService extends PluginService{

    use BootstrapCache;

    const FORBIDDEN_HOOKS = [
        "GET::sanctum/csrf-cookie",

        "GET::broadcasting/auth",
        "POST::broadcasting/auth",
    ];

    protected function fetch(): array {
        return Hook::all()->mapToGroups(function ($hook) {
            return [
                $hook->on => [
                    "id" => $hook->id,
                    "plugin_id" => $hook->plugin_id,
                    "plugin-name" => $hook->plugin->name ?? null,
                    "plugin-namespace" => $hook->plugin->getNamespace() ?? null,
                    "src" => $hook->src,
                ]
            ];
        })->toArray();
    }

    protected function getCacheName(): string {
        return 'plugin-hooks';
    }

    public function install(Plugin $plugin): void {
        $this->updateOrInstall($plugin);
    }

    public function update(Plugin $plugin): void {
        $this->updateOrInstall($plugin);
    }

    public function uninstall(Plugin $plugin): void {
        Hook::where('plugin_id', $plugin->id)->delete();
    }

    /**
     * Update hooks for a plugin.
     *
     * If a Plugin was injected into the service container it will be used,
     * otherwise pass the Plugin instance to this method.
     *
     * @param Plugin|null $plugin
     * @return void
     */
    public function updateOrInstall(Plugin $plugin): void {
        $manifest = PluginManifest::fromPlugin($plugin);
        $hookDefinitions = $manifest->getContent()['hooks'] ?? [];

        DB::transaction(function () use ($hookDefinitions, $plugin) {
            Hook::where('plugin_id', $plugin->id)->delete();
            foreach($hookDefinitions as $hookXml) {
                if(isset($hookXml['@attributes'])){
                $this->addHook($hookXml['@attributes'], $plugin);
                }
            }
        });

        $this->cache();
    }

    public function addHook(array $hook, Plugin $plugin) {
        $hookModel = self::createHookFromJson($hook, $plugin);

        $method = Method::parseFromString($hookModel->src);
        if(!$method->exists($plugin->getNamespace())) {
            $fullClass = $method->expandNamespace($plugin->getNamespace());
            throw new \Exception("Hook src '" . $hookModel->src . "' does not exist in plugin '" . $plugin->name . "', Class was resolved to: '$fullClass'");
        }

        $hookModel->save();
    }

    public function executeHooks(string $hookName, $request, $response) {
        $hooks = $this->getHooksFor($hookName);
        foreach($hooks as $hook) {
            try {
                $method = Method::parseFromString($hook["src"]);
                $fullClass = $method->expandNamespace($hook["plugin-namespace"]);
                $instance = app()->make($fullClass);
                // Call the hook method with the first argument passed by-reference so
                // hooks can modify the response or payload directly. Use call_user_func_array
                // to preserve reference semantics.
                call_user_func_array([$instance, $method->method], [$request, $response]);
            } catch(\Exception $e) {
                Log::error("Error executing hook '" . $hook["src"] . "' for plugin '" . $hook["plugin-name"] . "': " . $e->getMessage());
            }
        }
        return $response;
    }

    public function getHooksFor(string $hookName) {
        $hooks = $this->getData();
        return $hooks[$hookName] ?? [];
    }

    /**
     * When a plugin xml is parsed, this method is used to create a Hook model from
     * the json representation of the hook in the plugin's info.xml.
     *
     * @throws \Exception if the json is missing required fields or has invalid values.
     * @return Hook - The unsaved Hook model instance.
     */
    public function createHookFromJson(array $hookJson, ?Plugin $plugin = null): Hook {

        $validator = new HookValidator();
        $hookModel = $validator->validate($hookJson);

        if($plugin) {
            $hookModel->plugin_id = $plugin->id;
        }

        return $hookModel;
    }
}
