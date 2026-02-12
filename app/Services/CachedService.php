<?php

namespace App\Services;

use App\Plugin;
use Illuminate\Support\Facades\Cache;


/**
 * Abstract base class for services that use caching.
 */
abstract class CachedService {


    /**
     * The cache key used for storing data in the cache. 
     * Each subclass should define its own unique cache key.
     */
    abstract protected function getCacheKey(): string;

    
    /**
     * Clears the cache for this service. Should be called 
     * when changes are made that would affect the cached data.
     */
    public function clearCache(): void {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Updates the cache with new data. The callback should return 
     * the new data to be cached.
     *
     * @param callable $callback A callback function that returns the new data to be cached.
     */
    public function updateCache(callable $callback) {
        Cache::forget($this->getCacheKey());
        return Cache::rememberForever($this->getCacheKey(), $callback);
    }
    
    /**
     * Retrieves the cached data for this service. Returns null if the cache is empty.
      *
      * @return mixed The cached data, or null if the cache is empty.
     */
    public function getCachedData() {
        return Cache::get($this->getCacheKey());
    }
}