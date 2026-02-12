<?php

namespace App\Services;

use App\Plugin;
use App\Models\Plugin\Hook;
use App\Support\Method;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HookService extends CachedPluggableService {
    
    protected function getCacheKey(): string
    {
        return 'plugin_hooks';
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
            foreach($hooks as $hook) {
                $this->addHook($hook, $plugin);
            }
        });
        
        $this->updateCache(function() {
            return Hook::all();
        });
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
    
    public function executeHooks(string $hookName, ...$args) {
        info("Executing hooks for '$hookName'");
        $hooks = $this->getHooksFor($hookName);
        foreach($hooks as $hook) {
            try{
                info("Executing hook '".$hook->src."' for plugin '".$hook->plugin->name."'");
                $method = Method::parseFromString($hook->src);
                $fullClass = $method->expandNamespace($hook->plugin->getNamespace());
                $instance = app()->make($fullClass);
                // Call the hook method with the first argument passed by-reference so
                // hooks can modify the response or payload directly. Use call_user_func_array
                // to preserve reference semantics.
                $params = [&$args[0]];
                call_user_func_array([$instance, $method->method], $params);
            }catch(\Exception $e) {
                Log::error("Error executing hook '".$hook->src."' for plugin '".$hook->plugin->name."': " . $e->getMessage());
            }
        }
        return $args[0];
    }
    
    public function getHooksFor(string $hookName) {
        $hooks = $this->getCachedData();
        if($hooks === null) {
            $hooks = Hook::all();
            $this->updateCache(function() use ($hooks) {
                return $hooks;
            });
        }
        return $hooks->where('on', $hookName)->sortBy('order');
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
}