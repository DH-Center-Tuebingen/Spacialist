<?php

namespace App\Models\Plugin;

use App\Plugin;
use Illuminate\Database\Eloquent\Model;

class Hook extends Model
{
    public const AVAILABLE_HOOKS = [
        'App\Http\Controllers\HomeController@getGlobalData',
        'EditorController@setRelationInfo',
        'EditorController@getEntityType'
    ];        

    protected $table = 'plugin_hooks';

    protected $fillable = [
        'plugin_id',
        'src',
        'order',
        'on',
    ];
    
    /**
     * When a plugin xml is parsed, this method is used to create a Hook model from 
     * the json representation of the hook in the plugin's info.xml.
     * 
     * @throws \Exception if the json is missing required fields or has invalid values.
     * @return {Model(unsaved)} An unsaved Hook model instance.
     */
    public static function getHookFromJson($hookJson, Plugin $plugin = null): Hook {
        $hook = $hookJson;
        
        $missingFields = [];
        $requiredFields = ['on', 'src'];
        
        foreach($requiredFields as $reqiredField){
            if(!isset($hook[$reqiredField])) {
                $missingFields[] = $reqiredField;
                }
        }
        
        if(count($missingFields) > 0){
            info("MISSING FIELDS IN HOOK JSON: " . implode(", ", $missingFields) . " in hook json: " . json_encode($hookJson));
            throw new \Exception("Hook is missing field(s): " . implode(", ", $missingFields));
        }
        
        if(!in_array($hook['on'], self::AVAILABLE_HOOKS)) {
            throw new \Exception("Hook '".$hook['on']."' is not a valid hook.");
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
        $hookModel->src = $hook['src'];
        $hookModel->order = $order;
        
        if($plugin) {
            $hookModel->plugin_id = $plugin->id;
        }
        
        return $hookModel;
    }
}
