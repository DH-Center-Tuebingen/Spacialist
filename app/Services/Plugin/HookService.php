<?php
namespace App\Services\Plugin;

use App\Models\Plugin\Hook;
use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use App\Support\Method;
use App\Validators\HookValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * Service that manages plugin hooks. Hooks are a way for plugins to inject functionality 
 * into existing routes or other parts of the application without modifying core code.
 * 
 * A order can be specified that forces the execution of hooks a specific position compared
 * to other hooks. Hooks with a higher value will be executed later than hooks with a lower value. 
 * So that later modifications will overwrite previous ones. The default order value is 0 and by default they are executed in the order 
 * of their creation.
 * 
 * ```xml
 * <Hooks>
 *     <Hook on="api/vx/target/endpoint" src="Class@method" order="1"/>
 *     ...
 * </Hooks>
 * ```
 */
class HookService extends PluginService {

    use BootstrapCache;

    const FORBIDDEN_HOOKS = [
        "GET::sanctum/csrf-cookie",

        "GET::broadcasting/auth",
        "POST::broadcasting/auth",
    ];

    protected function fetch(): array {
        return Hook::with('plugin')
            ->get()
            ->mapToGroups(function ($hook) {
                $pluginName = $hook->plugin?->name ?? null;
                $pluginNamespace = $pluginName ? PluginDirectory::namespaceOf($pluginName) : null;

                return [
                    $hook->on => [
                        "id" => $hook->id,
                        "plugin_id" => $hook->plugin_id,
                        "plugin-name" => $pluginName,
                        "plugin-namespace" => $pluginNamespace,
                        "src" => $hook->src,
                        "order" => $hook->order
                    ]
                ];
            })->toArray();
    }

    protected function getCacheName(): string {
        return 'plugin-hooks';
    }

    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $this->updateOrInstall($plugin);
    }

    public function update(Plugin $plugin, PluginManifest $manifest): void {
        $this->updateOrInstall($plugin);
    }

    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
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
        $hookDefinitions = $manifest->getTagNodes('hooks/hook') ?? [];

        DB::transaction(function () use ($hookDefinitions, $plugin) {
            Hook::where('plugin_id', $plugin->id)->delete();
            foreach($hookDefinitions as $hookXml) {
                if(isset($hookXml['attributes'])) {
                    $this->addHook($hookXml['attributes'], $plugin);
                }
            }
        });

        $this->cache();
    }

    public function addHook(array $hook, Plugin $plugin) {
        $hookModel = self::createHookFromJson($hook, $plugin);
        $pluginNamespace = PluginDirectory::namespaceOf($plugin->name);
        
        $method = Method::parseFromString($hookModel->src);
        if(!$method->exists($pluginNamespace)) {
            $fullClass = $method->expandNamespace($pluginNamespace);
            throw new \Exception("Hook src '" . $hookModel->src . "' does not exist in plugin '" . $plugin->name . "', Class was resolved to: '$fullClass'");
        }

        $hookModel->save();
    }

    /**
     * Executes all hooks on a specific route.
     * 
     * @param string $src - The source to execute hooks for(e.g. route name or other identifier)
     * @param mixed $request - The request object to pass to the hook methods
     * @param mixed $response - The response object to pass to the hook methods. This is passed by reference so hooks can modify it directly.
     */
    public function executeHooks(string $src, $request, $response) {
        $hooks = $this->getHooksFor($src);
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

    /**
     * Get's hooks for a specific source (e.g. route) and sorted by their order attribute.
     * @param string $src - The source to get hooks for(e.g. route name or other identifier)
     * @return array - An array of hooks matching the source, sorted by their order attribute (ascending). Hooks without an order attribute are treated as 0.
     */
    public function getHooksFor(string $src) {
        $hooks = $this->getData();
        $matchingHooks = $hooks[$src] ?? [];
        usort($matchingHooks, function ($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });

        return $matchingHooks;
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
