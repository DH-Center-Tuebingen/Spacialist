<?php
namespace App\Services\Plugin;

use App\Exceptions\PluginLifecycleException;
use App\Models\Plugin\Attribute as PluginAttribute;
use App\Plugin;
use App\Plugin\PluginManifest;
use App\Support\BootstrapCache;
use Exception;

/**
 * Adds the capability for plugins to register custom attributes.
 * 
 * '''xml
 * ...
 *    <attributes>
 *        <attribute src="/Path/To/Attribute/Class" />
 *    </attributes>
 * ...
 * '''
 */
class AttributeService extends PluginService{

    use BootstrapCache;
    
    public function getCacheName(): string
    {
        return 'plugin-attributes';
    }
    
    protected function fetch(): array
    {
        return PluginAttribute::all()->toArray();
    }
    
    public function getAttributesFromManifest(Plugin $plugin, PluginManifest $manifest): array {
        $attributeXmlNodes = $manifest->getTagNodes('attributes/attribute');
        $attributes = [];
        foreach($attributeXmlNodes as $attributeXmlNode) {

            if(!isset($attributeXmlNode['attributes']) || !isset($attributeXmlNode['attributes']['src'])) {
                throw new PluginLifecycleException($plugin, "Invalid attribute declaration in plugin manifest of {$manifest->getName()}. Missing 'src' attribute.");             
            }
        
            $src = (string) $attributeXmlNode['attributes']['src'];
            
            $attributes[] = [
                'src' => $src,
            ];
        }
    
        return $attributes;
    }
    
    public function install(Plugin $plugin, PluginManifest $manifest): void {
        $attributes = $this->getAttributesFromManifest($plugin, $manifest);

        foreach($attributes as $attribute) {
            
            $projectedClass = "App\\Plugins\\{$plugin->name}\\{$attribute['src']}";
        
            if(!class_exists($projectedClass)) {
                throw new PluginLifecycleException($plugin, "Attribute class '{$attribute['src']}' not found for plugin '{$manifest->getName()}'.");
            }
        
            PluginAttribute::create([
                'plugin_id' => $plugin->id,
                'src' => $projectedClass,
            ]);
        }
        
        $this->cache();
    }
    
    public function uninstall(Plugin $plugin, PluginManifest $manifest): void {
        PluginAttribute::where('plugin_id', $plugin->id)->delete();
        $this->cache();
    }
}
