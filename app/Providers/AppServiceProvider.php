<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::morphMap([
            'files' => 'App\File',
        ]);

        Validator::extend('boolean_string', function ($attribute, $value, $parameters, $validator) {
            $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false', 'TRUE', 'FALSE'];
            return in_array($value, $acceptable, true);
        });
        Validator::extend('color', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^#[a-fA-F0-9]{6}$/', $value, $matches) === 1;
        });
        Validator::extend('between_float', function ($attribute, $value, $parameters, $validator) {
            return $value >= $parameters[0] && $value <= $parameters[1];
        });
        // Geometry can be either one of the supported simple features
        // (*Point, *LineString and *Polygon)
        // or 'any'
        Validator::extend('geometry', function ($attribute, $value, $parameters, $validator) {
            $isActualGeometry = in_array($value, \App\Geodata::getAvailableGeometryTypes());
            if(!$isActualGeometry) {
                return $value == 'any';
            }
            return true;
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
