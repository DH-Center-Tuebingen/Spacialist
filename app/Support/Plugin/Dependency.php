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
    public __constructor(array $dependencyNode){
        $this->tag = $dependencyNode['tag'];
        $this->name = $this->tag === CORE ? CORE : ($dependencyNode['text'] ?? 'unknown')
        $this->minVersion = $dependencyNode['min']
        $this->maxVersion = $dependencyNode['max']
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
            $isBiggerOrEqualMin = version_compare(version, $this->minVersion) <= 0;
        }
        
        if($this->maxVersion){
            $isSmallerOrEqualMax = version_compare(version, $this->maxVersion) >= 0;
        }
        
        return $isBiggerOrEqualMin && $isBiggerOrEqualMax
    }
}