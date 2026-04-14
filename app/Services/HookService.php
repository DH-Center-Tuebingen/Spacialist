<?php

namespace App\Services;

use App\Plugin;
use App\Models\Plugin\Hook;
use App\Support\Method;
use App\Support\BootstrapCache;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class HookService {

    use BootstrapCache;

    const FORBIDDEN_HOOKS = [
        "GET::sanctum/csrf-cookie",

        "GET::broadcasting/auth",
        "POST::broadcasting/auth",
    ];

    protected function fetch(): array {
        return Hook::all()->mapToGroups(function ($hook) {
            return [$hook->on => [
                "id" => $hook->id,
                "plugin_id" => $hook->plugin_id,
                "plugin-name" => $hook->plugin->name ?? null,
                "plugin-namespace" => $hook->plugin->getNamespace() ?? null,
                "src" => $hook->src,
            ]];
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

    public function remove(Plugin $plugin): void {
        // No separate remove logic needed for hooks
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
        $info = $plugin->getInfo();
        $hookDefinitions = $info['hooks'] ?? [];

        DB::transaction(function () use ($hookDefinitions, $plugin) {
            Hook::where('plugin_id', $plugin->id)->delete();
            foreach($hookDefinitions as $hookXml) {
                $this->addHook($hookXml['@attributes'], $plugin);
            }
        });

        $this->cache();
    }

    public function addHook(array $hook, Plugin $plugin){
        $hookModel = self::createHookFromJson($hook, $plugin);
        
        $method = Method::parseFromString($hookModel->src);
        if(!$method->exists($plugin->getNamespace()) ) {
            $fullClass = $method->expandNamespace($plugin->getNamespace());
            throw new \Exception("Hook src '".$hookModel->src."' does not exist in plugin '".$plugin->name."', Class was resolved to: '$fullClass'");
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
        $hook = $this->ensureRequiredFields($hookJson);
        $hook = $this->trimFields($hook);
        $this->evaluateMethodFormat($hook);
        $method = $this->evaluateMethod($hook);
        $order = $this->evaluateOrder($hook);


        $hookModel = new Hook();
        $hookModel->on = $hook['on'];
        $hookModel->method = $method;
        $hookModel->src = $hook['src'];
        $hookModel->order = $order;

        $this->validateOn($hookModel);
        $this->validateRouteIsNotForbidden($hookModel);

        if($plugin) {
            $hookModel->plugin_id = $plugin->id;
        }

        return $hookModel;
    }

    function ensureRequiredFields(array $hookJson) {
        $missingFields = [];
        $requiredFields = ['on', 'src'];
        foreach($requiredFields as $requiredField) {
            if(!isset($hookJson[$requiredField])) {
                $missingFields[] = $requiredField;
            }
        }

        if(count($missingFields) > 0) {
            throw new \Exception("Hook is missing field(s): " . implode(", ", $missingFields));
        }
        return $hookJson;
    }

    function trimFields(array $hookJson) {
        $fieldsToTrim = ['on', 'src', 'method'];
        foreach($fieldsToTrim as $field) {
            if(isset($hookJson[$field]) && is_string($hookJson[$field])) {
                $hookJson[$field] = trim($hookJson[$field]);
            }
        }
        return $hookJson;
    }

    function evaluateMethodFormat(array $hookJson): void {
        $method = Method::parseFromString($hookJson['src']); // This will throw an exception if the format is invalid, we just use it for validation here.
        if(!$method->isValid()) {
            throw new \Exception("Hook 'src' field must be in the format 'class@method'. Given: '{$hookJson['src']}'");
        }
    }

    function evaluateMethod(array $hookJson): string {
        $method = isset($hookJson['method']) ? strtoupper($hookJson['method']) : "GET";

        if(!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            throw new \Exception("Hook 'on' field has invalid method '$method'. Allowed methods are GET, POST, PUT, DELETE, PATCH.");
        }

        return $method;
    }

    function evaluateOrder(array $hookJson): int {
        $order = isset($hookJson['order']) ? $hookJson['order'] : 0;
        return intval($order);
    }

    function validateOn(Hook $hookModel): void {
        if(!$this->routeExists($hookModel->method, $hookModel->on)) {
            throw new \Exception("Hook on invalid route '" . $hookModel->getApiIdentifier() . "'.");
        }
    }

    function validateRouteIsNotForbidden(Hook $hookModel): void {
        if(in_array($hookModel->getApiIdentifier(), self::FORBIDDEN_HOOKS)) {
            throw new \Exception("Hook on '" . $hookModel->getApiIdentifier() . "' is not allowed.");
        }
    }

    private function routeExists(string $method, string $url): bool {
        $methodRoutes = Route::getRoutes()->getRoutesByMethod()[$method] ?? [];
        return isset($methodRoutes[$url]);
    }
}