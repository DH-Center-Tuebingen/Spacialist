<?php

namespace App;

class VersionInfo {
    private static $file = '.VERSION';
    // Semantic versioning
    private $major = 1;
    private $minor = 0;
    private $patch = 0;

    private $release = "v$major.$minor.$patch";
    private $releaseName = "No Release Title";
    private $releaseHash;

    function __construct() {
        if(file_exists($file)) {
            $content = file_get_contents($file);
            // content should have this format "v0.5.0-ephesus-16-g7109034"
            $parts = explode('-', $content);
            if(count($parts) != 4) {
                return;
            }
            $release = $parts[0];
            $releaseName = ucfirst($parts[1]);
            $releaseHash = $parts[3];
        }
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
        return "$this->release-$this->releaseName-$this->releaseHash";
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
}
