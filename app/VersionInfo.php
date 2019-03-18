<?php

namespace App;

class VersionInfo {
    // Semantic versioning
    private $major;
    private $minor;
    private $patch;

    private $release;
    private $releaseName;
    private $releaseHash;

    private $time;

    function __construct() {
        exec('git describe --tags', $tag, $exitcode);
        exec('git log -1 --format=%at', $ts, $exitcodeTs);
        if($exitcode === 0 && $exitcodeTs === 0) {
            $content = [
                $tag[0], $ts[0]
            ];
            $parts = explode('-', $content[0]);
            $this->release = $parts[0];
            $this->releaseName = ucfirst($parts[1]);
            if(count($parts) >= 4) $this->releaseHash = $parts[3];
            // cut off 'v' for semantic versioning
            $semVer = explode('.', substr($this->release, 1));
            $this->major = $semVer[0];
            $this->minor = $semVer[1];
            $this->patch = $semVer[2];

            $this->time = $content[1];
        } else {
            $this->major = '?';
            $this->minor = '?';
            $this->patch = '?';
            $this->release = 'v?.?.?';
            $this->releaseName = 'No Release Found';
            $this->releaseHash = '?';
            $this->time = time();
            return;
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
        $releaseName = strtolower($this->releaseName);
        $release = "$this->release-$releaseName";
        if(isset($this->releaseHash)) {
            $release .= "-$this->releaseHash";
        }
        return $release;
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
