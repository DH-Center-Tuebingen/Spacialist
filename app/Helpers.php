<?php

namespace App;

use App\Bibliography;

class Helpers {
    public static function computeCitationKey($l) {
        $key;
        if($l['author'] != null) {
            $key = $l['author'];
        } else {
            $key = $l['title'];
        }
        // Use first two letters of author/title as key with only first letter uppercase
        $key = ucwords(mb_strtolower(substr($key, 0, 2))) . ':';
        if($l['year'] != null) {
            $key .= $l['year'];
        } else {
            $key .= '0000';
        }

        $initalKey = $key;
        $suffixes = array_merge(range('a', 'z'), range('A', 'Z'));
        $suffixesCount = count($suffixes);
        $i = 0;
        $j = 0;
        while(Bibliography::where('citekey', $key)->first() !== null) {
            // if single letter was not enough to be unique, add another
            if($i == $suffixesCount) {
                if($j == $suffixesCount) $j = 0;
                $initalKey = $initalKey . $suffixes[$j++];
                $i = 0;
            }
            $key = $initalKey . $suffixes[$i++];
        }
        return $key;
    }

    public static function parseBoolean($str) {
        $acceptable = [true, 1, '1', 'true', 'TRUE'];
        return in_array($str, $acceptable, true);
    }
}
