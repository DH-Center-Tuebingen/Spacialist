<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;


/**
 * Stores a compiled set of data, directly as php file inside Laravel's 'bootstrap/cache' 
 * directory to allow fast access with little to no overhead.
 */
trait BootstrapCache
{

    /**
    * Cached data.
    */
    protected ?array $data = null;
    
    /**
     * Retrieves the cached data. If the cache is not loaded, it will attempt to load it from disk.
     * If the cache file does not exist or is invalid, it will throw an exception.
     */
    public function getData(): array {
        if($this->data !== null) {
            return $this->data;
        }

        try{
            $this->cache();
        } catch(\Exception $e) {
            if(! File::exists($this->getAppPath())) {
                throw new \Exception(
                    static::class . ' could not rebuild cache. File not found. Original error: ' . $e->getMessage()
                );
            }
        }

        $data = require $this->getAppPath();

        if(! is_array($data)) {
            $data = $this->fetch();
            $this->cache();
        }

        return $this->data = $data;
    }

    /**
     * Returns the relative path inside 'bootstrap/cache' where the cache should be stored.
     */
    abstract protected function getCacheName(): string;

    /**
     * Defines how the cache should be built from the source of truth. 
     * This is used when the cache is built or rebuilt: e.g. from the Database.
     */
    abstract protected function fetch(): array;

    /**
     * Build the cache array from the source of truth.
     */
    protected function cache(): array {
        File::ensureDirectoryExists(dirname($this->getAppPath()));

        $this->data = $this->fetch();
        File::put(
            $this->getAppPath(),
            $this->export($this->data)
        );

        return $this->data;
    }

    /**
     * Load the cache from disk.
     * This should be used when the cache is used in Laravel boot
     * and the services are not yet fully booted. 
     * 
     * Otherwise use the get() method as it automtically loads the cache if not already loaded.
     */
    public function load(): array
    {
        if($this->data !== null) {
            return $this->data;
        }

        if(! File::exists($this->getAppPath())) {
            throw new RuntimeException(
                static::class . ' cached data not found. Run build.'
            );
        }

        $data = require $this->getAppPath();

        if(! is_array($data)) {
            throw new RuntimeException(
                static::class . ' cached data is invalid.'
            );
        }

        return $this->data = $data;
    }

    /**
     * Get's the absolute system path to the cache file.
     * @return string
     */
    protected function getAppPath(): string {
        if(!$this->getCacheName()) {
            throw new RuntimeException(
                static::class . ' cache path is not defined.'
            );
        }

        // To ensure we don't use the same cache file for testing and production, 
        // we append '-testing' to the file name when in testing environment.
        $fileName = $this->getCacheName();
        if(env('APP_ENV') === 'testing') {
            $fileName = $fileName . '-testing';
        }

        $phpFile = Str::finish($fileName, '.php');
        return base_path('bootstrap' . DIRECTORY_SEPARATOR . 'cache' .  Str::start($phpFile, DIRECTORY_SEPARATOR));
    }
    
    /**
     * Get's the absolute path to the cache file.
     * @return string - absolute system path to the cache file
     */
    public function getCachedFilePath(): string {
        return $this->getAppPath();
    }
    
    /**
     * Determine if the cache exists.
     */
    public function cacheExists(): bool
    {
        return File::exists($this->getAppPath());
    }

    /**
     * Delete the cache.
     */
    public function clearCache(): void
    {
        File::delete($this->getAppPath());
        $this->data = null;
    }

    /**
     * Export array as a PHP file.
     */
    protected function export(array $data): string
    {
        return <<<PHP
<?php
return {$this->varExport($data)};
PHP;
    }

    /**
     * Isolated var_export for easy override.
     */
    protected function varExport(array $data): string
    {
        return var_export($data, true);
    }
}