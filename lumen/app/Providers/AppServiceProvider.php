<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot() {
        // Validator::extend('', function ($attribute, $value, $parameters, $validator) {
        //     return $value == '';
        // });
        Validator::extend('geom_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, \App\Geodata::availableGeometryTypes);
        });
    }
}
