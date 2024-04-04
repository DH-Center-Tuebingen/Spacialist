<?php

namespace App\Providers;

use App\AttributeTypes\SiUnitAttribute;
use App\Bibliography;
use App\Geodata;
use App\Preference;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('geography', 'string');
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'string');

        // In some Proxy setups it might be necessary to enforce using the app's url as root url
        if(env('APP_FORCE_URL') === true) {
            $rootUrl = config('app.url');
            URL::forceRootUrl($rootUrl);
            if(Str::startsWith($rootUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        Paginator::useBootstrap();
        
        Relation::morphMap([
            'attribute_values' => 'App\AttributeValue'
        ]);
        
        View::composer('*', function($view) {
            $preferences = Preference::all();
            $preferenceValues = [];
            foreach($preferences as $p) {
                $preferenceValues[$p->label] = Preference::decodePreference($p->label, json_decode($p->default_value));
            }

            $view->with('p', $preferenceValues);
        });

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
        Validator::extend('orcid', function ($attribute, $value, $parameters, $validator) {
            if(preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[0-9Xx]$/', $value, $matches) !== 1 && preg_match('/^\d{15}[0-9Xx]$/', $value, $matches) !== 1) {
                return false;
            }
            $strippedValue = str_replace('-', '', $value);

            $total = 0;
            for($i=0; $i<strlen($strippedValue)-1; $i++) {
                $val = intval($strippedValue[$i]);
                $total = ($total + $val) * 2;
            }
            $chk = (12 - ($total % 11)) % 11;
            if($chk == 10) $chk = 'X';

            return strtoupper(substr($strippedValue, -1)) == $chk;
        });
        // Geometry can be either one of the supported simple features
        // (*Point, *LineString and *Polygon)
        // or 'any'
        Validator::extend('geometry', function ($attribute, $value, $parameters, $validator) {
            $isActualGeometry = in_array($value, Geodata::getAvailableGeometryTypes());
            if(!$isActualGeometry) {
                return $value == 'Any';
            }
            return true;
        });
        Validator::extend('mod_action', function($attribute, $value, $parameters, $validator) {
            $lowVal = strtolower($value);
            return $lowVal == 'accept' || $lowVal == 'deny';
        });
        Validator::extend('bibtex_type', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, array_keys(Bibliography::bibtexTypes));
        });
        Validator::extend('si_baseunit', function ($attribute, $value, $parameters, $validator) {
            return in_array($value, array_keys(SiUnitAttribute::getUnits()));
        });
        Validator::extend('si_unit', function ($attribute, $value, $parameters, $validator) {
            if(count($parameters) != 1) {
                return false;
            }
            $refField = request()->input($parameters[0]);
            $baseunits = SiUnitAttribute::getUnits();
            if(!in_array($refField, array_keys($baseunits))) {
                return false;
            }
            $units = $baseunits[$refField]['units'];
            foreach($units as $unit) {
                if($unit['label'] == $value) {
                    return true;
                }
            }

            return false;
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
