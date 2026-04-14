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
    
    protected function fetch(): array
    {
        $hooksByMethod = [];
        
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

    protected function getCacheName(): string
    {
        return 'plugin-hooks';
    }
        
    public function install(Plugin $plugin): void
    {
        $this->updateOrInstall($plugin);
    }
    
    public function update(Plugin $plugin): void
    {
        $this->updateOrInstall($plugin);
    }
    
    public function uninstall(Plugin $plugin): void
    {
        Hook::where('plugin_id', $plugin->id)->delete();
    }
    
    public function remove(Plugin $plugin): void
    {
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
    public function updateOrInstall(Plugin $plugin): void
    {
        $info = $plugin->getInfo();
        $hooks = $info['hooks'] ?? [];
        
        DB::transaction(function() use ($hooks, $plugin) {
            Hook::where('plugin_id', $plugin->id)->delete();
            foreach($hooks as $hookXml) {
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
            try{
                $method = Method::parseFromString($hook["src"]);
                $fullClass = $method->expandNamespace($hook["plugin-namespace"]);
                $instance = app()->make($fullClass);
                // Call the hook method with the first argument passed by-reference so
                // hooks can modify the response or payload directly. Use call_user_func_array
                // to preserve reference semantics.
                call_user_func_array([$instance, $method->method], [$request, $response]);
            }catch(\Exception $e) {
                Log::error("Error executing hook '".$hook["src"]."' for plugin '".$hook["plugin-name"]."': " . $e->getMessage());
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
     * @return {Model(unsaved)} An unsaved Hook model instance.
     */
    public function createHookFromJson(array $hookJson, ?Plugin $plugin = null): Hook {
        $hook = $hookJson;
        
        $missingFields = [];
        $requiredFields = ['on', 'src'];
        foreach($requiredFields as $requiredField){
            if(!isset($hook[$requiredField])) {
                $missingFields[] = $requiredField;
            } else {
                $hook[$requiredField] = trim($hook[$requiredField]);
            }
        }
        
        if(count($missingFields) > 0){
            throw new \Exception("Hook is missing field(s): " . implode(", ", $missingFields));
        }
        
        // If no method is specified in the 'on' field, default to GET
        $method = isset($hook['method']) ? strtoupper($hook['method']) : "GET";
        
        if(!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            throw new \Exception("Hook 'on' field has invalid method '$method'. Allowed methods are GET, POST, PUT, DELETE, PATCH.");
        }
        
        info($method);
        if(!$this->routeExists($method, $hook['on'])) {
            throw new \Exception("Hook on invalid route  '".$method."::".$hook['on']."'.");
        }

        $src = $hook['src'];
        $parts = explode('@', $src);
        if(count($parts) != 2) {
            throw new \Exception("Hook 'src' field must be in the format 'class@method'");
        }

        $order = isset($hook['order']) ? $hook['order'] : 0;
        $order = intval($order);

        $hookModel = new Hook();
        $hookModel->on = $hook['on'];
        $hookModel->method = $method;
        $hookModel->src = $hook['src'];
        $hookModel->order = $order;
        
        if(in_array($hookModel->getApiIdentifier(), self::FORBIDDEN_HOOKS)) {
            throw new \Exception("Hook on '".$hook['on']."' is not allowed.");
        }
        
        if($plugin) {
            $hookModel->plugin_id = $plugin->id;
        }
        
        return $hookModel;
    }
    
    private function routeExists(string $method, string $url) {
        $methodRoutes = Route::getRoutes()->getRoutesByMethod()[$method] ?? [];
        info($url);
        info(array_keys($methodRoutes));
        return isset($methodRoutes[$url]);
    }
}