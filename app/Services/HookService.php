<?php

namespace App\Services;

use App\Plugin;
use App\Models\Plugin\Hook;
use App\Support\Method;
use App\Support\BootstrapCache;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HookService extends BootstrapCache {

    public function __construct() {
        parent::__construct();
    }
    
    protected function fetch(): array
    {
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

    protected function getPath(): string
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
        $hookModel = Hook::getHookFromJson($hook, $plugin);
        
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
        $hooks = $this->get();
        return $hooks[$hookName] ?? [];
    }

}