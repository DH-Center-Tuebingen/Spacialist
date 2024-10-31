<?php

use App\Plugin;
use App\ThConcept;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

if(!function_exists('sp_remove_dir')) {
    function sp_remove_dir($dir) {
        if(is_dir($dir)){
            foreach(glob("{$dir}/*") as $file) {
                if(is_dir($file)) {
                    sp_remove_dir($file);
                } else if(!is_link($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
    }
}

if(!function_exists('sp_slug')) {
    function sp_slug($str) {
        return Str::slug($str);
    }
}

if(!function_exists('sp_trim_array')) {
    function sp_trim_array(array $arr) : array {
        return array_map(fn($elem) => trim($elem), $arr);
    }
}

if(!function_exists('sp_get_permission_groups')) {
    function sp_get_permission_groups($onlyGroups = false) {
        $corePermissionPath = base_path("storage/framework/App/core-permissions.json");
        if(!File::isFile($corePermissionPath)) {
            return response()->json([]);
        }
        $permissionSets = json_decode(file_get_contents($corePermissionPath), true);

        if($onlyGroups) {
            return array_keys($permissionSets);
        } else {
            return $permissionSets;
        }
    }
}

if(!function_exists('sp_get_themes')) {
    function sp_get_themes() {
        $themeDir = base_path("resources/sass/");
        $fileList = glob($themeDir . "app*.scss");
        $themes = [];
        foreach($fileList as $file) {
            $theme = [];
            // cut off base path and .scss extension
            $fileName = substr($file, strlen($themeDir), -5);
            // default stylesheet needs special treatment
            if($fileName == 'app') {
                $theme['id'] = 'default';
                $theme['key'] = '';
            } else {
                $extName = substr($fileName, 3);
                $theme['id'] = substr($extName, 1);
                $theme['key'] = $extName;
            }
            $themes[] = $theme;
        }
        return $themes;
    }
}

if(!function_exists('sp_has_plugin')) {
    function sp_has_plugin($name) {
        try {
            Plugin::where('name', $name)->whereNotNull('installed_at')->firstOrFail();
            return true;
        } catch(ModelNotFoundException $e) {
            return false;
        }
    }
}

if(!function_exists('sp_loggable_models')) {
    function sp_loggable_models() {
        $loggableModels = [];
        $listing = scandir(__DIR__);
        foreach($listing as $entry) {
            if(!Str::endsWith($entry, '.php')) continue;
            if(substr($entry, 0, 1) !== Str::ucfirst(substr($entry, 0, 1))) continue;

            $className = str_replace('.php', '', $entry);

            $traits = class_uses("App\\$className");
            if($traits === false) continue;

            if(
                isset($traits['Spatie\\Activitylog\\Traits\\LogsActivity'])
                &&
                $traits['Spatie\\Activitylog\\Traits\\LogsActivity'] === 'Spatie\\Activitylog\\Traits\\LogsActivity'
            ) {
                $loggableModels[] = [
                    'id' => "App\\$className",
                    'label' => $className
                ];
            }
        }

        return $loggableModels;
    }
}

if(!function_exists('sp_copyname')) {
    function sp_copyname($name) {
        return $name . " - " . __("Copy");
    }
}

if(!function_exists('sp_parse_boolean')) {
    function sp_parse_boolean($str) {
        $acceptable = [true, 1, '1', 'true', 'TRUE'];
        return in_array($str, $acceptable, true);
    }
}

if(!function_exists('sp_get_public_url')) {
    function sp_get_public_url($filename) {
        return Storage::url($filename);
    }
}

if(!function_exists('sp_get_storage_file_path')) {
    function sp_get_storage_file_path($filename) {
        return Storage::path($filename);
    }
}

if(!function_exists('sp_has_exif')) {
    function sp_has_exif($exif, $rootKey, $dataKey) {
        return array_key_exists($rootKey, $exif) && array_key_exists($dataKey, $exif[$rootKey]);
    }
}

if(!function_exists('sp_raw_query')) {
    function sp_raw_query($builder) {
        $sql = $builder->toSql();
        $bindings = $builder->getBindings();
        foreach($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}

if(!function_exists('sp_column_names')) {
    function sp_column_names($table) {
        switch($table) {
            case 'attributes':
                return \DB::table('information_schema.columns')
                    ->select('column_name')
                    ->where('table_name', $table)
                    ->where('table_schema', 'public')
                    ->get()
                    ->pluck('column_name')
                    ->toArray();
            default:
                return Schema::getColumnListing($table);
        }
    }
}

// detail level
// 0 = no relations
// 1 = labels
// 2 = all
if(!function_exists('th_tree_builder')) {
    function th_tree_builder($langCode = null, $detailLevel = 2) {
        $builder = ThConcept::query();
        if($detailLevel === 0 || $langCode === null) {
            return $builder;
        } else {
            $labelWith = [
                'labels.language' => function($query) use($langCode) {
                    $query->orderByRaw("short_name = '$langCode' desc");
                }, 'labels.concept'
            ];
            return $builder->with($labelWith);
        }
    }
}

if(!function_exists('sp_has_analysis')) {
    function sp_has_analysis() {
        $analysisDir = base_path("../analysis");
        return is_dir($analysisDir);
    }
}
