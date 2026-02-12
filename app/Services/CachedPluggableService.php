<?php

namespace App\Services;

use App\Plugin;
use App\Interfaces\IPluggable;
use Illuminate\Support\Facades\Cache;


/**
 * Abstract base class for plugin services that use caching.
 */
abstract class CachedPluggableService extends CachedService implements IPluggable { }