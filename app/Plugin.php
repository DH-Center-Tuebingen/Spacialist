<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Plugin extends Model
{
    /**
     * The attributes that are assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    protected $metadataFields = [
        'authors',
        'description',
        'licence',
        'title',
    ];

    public static function getInstalled() {
        return self::whereNotNull('installed_at')->get();
    }

    public function loadScript($name) {
        $scriptPath = base_path("app/Plugins/$this->name/js/$name");
        if (!File::isFile($scriptPath)) {
            return '';
        }

        $xmlString = file_get_contents($scriptPath);
        return $xmlString;
    }

    public static function getInfo($path) {
        $infoPath = Str::finish($path, '/') . 'App/info.xml';
        if(!File::isFile($infoPath)) return false;

        $xmlString = file_get_contents($infoPath);
        $xmlObject = simplexml_load_string($xmlString);
        
        return json_decode(json_encode($xmlObject), true);
    }

    public function getMetadata() {
        $info = self::getInfo(base_path("app/Plugins/$this->name"));
        if($info !== false) {
            $metadata = [];
            foreach($this->metadataFields as $field) {
                $metadata[$field] = $info[$field];
            }
            return $metadata;
        } else {
            return [];
        }
    }
}
