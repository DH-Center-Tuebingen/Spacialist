<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('boolean_string', function ($attribute, $value, $parameters, $validator) {
            $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false', 'TRUE', 'FALSE'];
            return in_array($value, $acceptable, true);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
