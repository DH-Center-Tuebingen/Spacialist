<?php

namespace App;

class File {

    static function getBomBytes() : string {
        return "\xEF\xBB\xBF";
    }

    static function hasBom($handle) : bool {
        $bom = fread($handle, 3);
        return $bom == self::getBomBytes();
    }

    static function removeBomIfNecessary(string $filename) : void {
        $file = fopen($filename, "r");

        if(self::hasBom($file)) {
            $outfileName = $filename . ".bom.tmp";
            $outfile = fopen($outfileName, "w");
            fseek($file, 3);

            // Copy the file without the BOM
            while(!feof($file)) {
                fwrite($outfile, fread($file, 8192));
            }

            fclose($file);
            fclose($outfile);
            rename($outfileName, $filename);
        } else {
            fclose($file);
        }
    }
}