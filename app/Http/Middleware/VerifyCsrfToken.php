<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Cookie;

use App\Utils\CookieUtils;

// This used to be called VerifyCsrfToken, but the Laravel Team uses the alias
// ValidateCsrfToken in the Documentation (10.x -> 11.x), so we updated the import.
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /*
    Recommendations online always reference the "addCookieToResponse" method,
    but the newCookie method is only used in the "addCookieToResponse" method.
    And must be applicable to any newXsrfToken Token so it makes no sense modifying
    the "addCookieToResponse" method, as it could have further implications when
    the code is updated in the future.

    protected function addCookieToResponse($request, $response) {
        $response = parent::addCookieToResponse($request, $response);
        return $response;
    }
    */

    /*
    When inspecting the base class you may find the 'XSRF-TOKEN' being used by the
    serialized function like this:

    public static function serialized()
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }

    Don't worry about this as the parameter is never used and it just returns a boolean!
    */

    /**
     * (!) NOTE: This is copied from the original Laravel 12.x implementation.
     * As there is no convenient way to overwrite the cookie name,
     * therefore we overwrite the newCookie method (with an identical implementation)
     * and just change the cookie name. If future versions of Laravel change the
     * implementation of this method, we will have to adapt this method as well.
     * If laravel allows for changing the cookie name in the future,
     * we can remove this method and use the original implementation!
     *
     * Create a new "XSRF-TOKEN" cookie that contains the CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $config
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function newCookie($request, $config) {
        return new Cookie(
            CookieUtils::xsrfName(),
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }
}
