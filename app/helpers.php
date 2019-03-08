<?php

use App\Bibliography;
use App\ThConcept;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
                    ->pluck('column_name');
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
