<?php

namespace App;

class VersionInfo {
    private static $file = '.VERSION';
    // Semantic versioning
    private $major;
    private $minor;
    private $patch;

    private $release;
    private $releaseName;
    private $releaseHash;

    private $time;

    function __construct() {
        $dir = dirname(__FILE__);
        $fullpath = "$dir/" . self::$file;
        if(file_exists($fullpath)) {
            $content = explode("\n", file_get_contents($fullpath));
            // \Log::info($content);
        } else {
            exec('git describe --tags', $tag, $exitcode);
            exec('git log -1 --format=%at', $ts, $exitcodeTs);
            if($exitcode === 0 && $exitcodeTs === 0) {
                $content = [
                    $tag[0], $ts[0]
                ];
            } else {
                $content = [
                    'v0.0.0-unreleased-1-gNOHASH',
                    time()
                ];
            }
        }
        // content should have this format "v0.5.0-ephesus-16-g7109034"
        $parts = explode('-', $content[0]);
        if(count($parts) != 4) {
            return;
        }
        $this->release = $parts[0];
        $this->releaseName = ucfirst($parts[1]);
        $this->releaseHash = $parts[3];
        // cut off 'v' for semantic versioning
        $semVer = explode('.', substr($this->release, 1));
        $this->major = $semVer[0];
        $this->minor = $semVer[1];
        $this->patch = $semVer[2];

        $this->time = $content[1];
    }

    public function getRelease() {
        return $this->release;
    }

    public function getReleaseName() {
        return $this->releaseName;
    }

    public function getReleaseHash() {
        return $this->releaseHash;
    }

    public function getReadableRelease() {
        return "$this->release ($this->releaseName)";
    }

    public function getFullRelease() {
        $releaseName = strtolower($this->releaseName);
        return "$this->release-$releaseName-$this->releaseHash";
    }

    public function getMajor() {
        return $this->major;
    }

    public function getMinor() {
        return $this->minor;
    }

    public function getPatch() {
        return $this->patch;
    }

    public function getTime() {
        return $this->time;
    }
}
