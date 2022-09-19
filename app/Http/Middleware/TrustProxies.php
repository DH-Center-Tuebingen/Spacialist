<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array
     */
    protected $proxies;

    /**
     * The current proxy header mappings.
     *
     * @var array
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
    // protected $headers = [
    //     Request::HEADER_FORWARDED => 'FORWARDED',
    //     Request::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
    //     Request::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
    //     Request::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
    //     Request::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
    // ];
}
