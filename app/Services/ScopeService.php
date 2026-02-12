<?php

namespace App\Services;

use App\Plugin;
use Illuminate\Support\Facades\Cache;

class ScopeService extends CachedService {
    
    protected function getCacheKey(): string {
        return 'scopes';
    }

    public function get(): array {
        
    }

    private function loadAccessPointsFromPlugins(): array {
        
    }
}