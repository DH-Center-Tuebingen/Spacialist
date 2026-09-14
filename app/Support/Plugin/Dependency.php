<?php

namespace App\Support\Plugin;

/**
 * Support class to manage the manifest dependencies.
 */
class Dependency {
    const CORE = 'core';    

    public readonly string $tag;
    public readonly string $name;
    public readonly ?string $minVersion;
    public readonly ?string $maxVersion;
    
    /**
     * Takes an dependency node array and transforms it into a Dependency object.
     * 
     * @param array $dependencyNode
     * 
     */
    public function __construct(array $dependencyNode){
        $this->tag = $dependencyNode['tag'];
        
        if($dependencyNode['attributes']){
            $attributes = $dependencyNode['attributes'];
            $this->name = $this->tag === self::CORE ? self::CORE : ($attributes['name'] ?? 'INVALID_DEPENDENCY');
            $this->minVersion = isset($attributes['min']) ? (string)$attributes['min'] : null;
            $this->maxVersion = isset($attributes['max']) ? (string)$attributes['max'] : null;
        }
    }
    
    
    /**
     * Compares a version with the min and max values of the dependency.
     * @return bool - If the version is inside the range, it returns true, otherwise false.
     */
    public function supportsVersion(?string $version): bool{
        if(!$version) return false;    
    
        $isBiggerOrEqualMin = true;
        $isSmallerOrEqualMax = true;
    
        
        if($this->minVersion){
            $isBiggerOrEqualMin = version_compare($this->minVersion, $version) <= 0;
        }
        
        if($this->maxVersion){
            $isSmallerOrEqualMax = version_compare($version, $this->maxVersion) <= 0;
        }
        return $isSmallerOrEqualMax && $isBiggerOrEqualMin;
    }
}