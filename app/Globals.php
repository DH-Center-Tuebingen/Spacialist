<?php

namespace App;

use Illuminate\Support\Facades\DB;

# Plugins
use \App\Plugins\Map\App\Geodata;

class Globals {
    public static function getTags(): array {
        $tagObj = Preference::where('label', 'prefs.tag-root')
            ->value('default_value');
        $tagUri = json_decode($tagObj)->uri;
        return DB::select("
            WITH RECURSIVE
            top AS (
                SELECT br.narrower_id as id, c2.concept_url
                FROM th_broaders br
                JOIN th_concept c ON c.id = br.broader_id
                JOIN th_concept c2 ON c2.id = br.narrower_id
                WHERE c.concept_url = '$tagUri'
                UNION
                SELECT br.narrower_id as id, c.concept_url
                FROM top t, th_broaders br
                JOIN th_concept c ON c.id = br.narrower_id
                WHERE t.id = br.broader_id
            )
            SELECT *
            FROM top
            ORDER BY id
        ");
    }

    public static function getVersion(): array {
        $versionInfo = new VersionInfo();
        return [
            'full' => $versionInfo->getFullRelease(),
            'readable' => $versionInfo->getReadableRelease(),
            'release' => $versionInfo->getRelease(),
            'name' => $versionInfo->getReleaseName(),
            'time' => $versionInfo->getTime()
        ];
    }

    public static function getGeometryTypes(): array {
        if(Plugin::isInstalled('Map')) {
            $types = Geodata::getAvailableGeometryTypes();
        } else {
            return [];
        }
    }
}
