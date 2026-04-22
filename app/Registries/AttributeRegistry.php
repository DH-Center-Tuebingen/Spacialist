<?php

namespace App\Registries;

use App\AttributeTypes\AttributeBase;
use App\Plugin;
use App\Services\PluginManager;
use Illuminate\Support\Arr;

class AttributeRegistry {
    private static array $types = [];

    private static function reset(): void {
        self::$types = [];
    }

    private static function register(AttributeBase $type, ?string $pluginName = null): void {
        $key = $type::getType();
        if(array_key_exists($key, self::$types)) {
            throw new \Exception("Attribute type '{$key}' is already registered.");
        }
        self::$types[$key] = [
            'class' => $type::class,
        ];
        if(isset($pluginName)) {
            self::$types[$key]['plugin'] = $pluginName;
        }
    }

    private static function registerCoreTypes(): void {
        $files = glob(base_path('/app/AttributeTypes') . '/*.php');
        foreach($files as $file) {
            $className = basename($file, '.php');
            $class = "App\\AttributeTypes\\{$className}";

            // Skip all abstract classes
            if((new \ReflectionClass($class))->isAbstract()) continue;

            self::register(new $class());
        }
    }

    private static function registerPluginTypes(): void {
        $installedPlugins = app(PluginManager::class)->getInstalledPlugins();

        foreach($installedPlugins as $plugin) {
            // $attributeTypes = $plugin->getRegisteredAttributes();
            // foreach($attributeTypes as $attributeType) {
            //     $AttributeNamespace = "App\\Plugins\\{$plugin->name}\\" . $attributeType['@attributes']['src'];

            //     if(!class_exists($AttributeNamespace)) {
            //         info("Attribute class '{$AttributeNamespace}' does not exist.");
            //         continue;
            //     }
                
            //     if(!is_subclass_of($AttributeNamespace, AttributeBase::class)) {
            //         info("Attribute class '{$AttributeNamespace}' is not a subclass of AttributeBase.");
            //         continue;
            //     }

            //     self::register(new $AttributeNamespace(), $plugin->name);
            // }
        }
    }

    public static function getTypes(bool $serialized = false, array $filters = []): array {
        self::reset();
        self::registerCoreTypes();
        self::registerPluginTypes();

        if(count($filters) > 0) {
            $types = Arr::where(self::$types, function(array $typeDef, string $key) use($filters) {
                $attr = $typeDef['class'];
                foreach($filters as $on => $value) {
                    if($on == "datatype") {
                        if($attr::getType() != $value) return false;
                    } else if($on == "in_table") {
                        if($attr::getInTable() != $value) return false;
                    } else if($on == "field") {
                        if($attr::getField() != $value) return false;
                    } else if($on == "has_selection") {
                        if($attr::getHasSelection() != $value) return false;
                    }
                }
                return true;
            });
        } else {
            $types = self::$types;
        }

        if($serialized) {
            return array_values(array_map(function(array $typeDef) {
                $data = $typeDef['class']::serialized();
                if(array_key_exists('plugin', $typeDef) && isset($typeDef['plugin'])) {
                    $data['plugin'] = $typeDef['plugin'];
                    $data['label'] = $typeDef['class']::getLabel();
                }
                return $data;
            }, $types));
        } else {
            return $types;
        }
    }
}