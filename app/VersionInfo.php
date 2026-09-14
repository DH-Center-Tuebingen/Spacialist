<?php

namespace App;

use Illuminate\Support\Str;

class VersionInfo {
    function __construct(
        private int $major = 0,
        private int $minor = 0, 
        private int $patch = 0,
        private string $release ="",
        private string $releaseName ="",
        private ?string $releaseHash = null,
        private string $time = "",
    ) {}

    /**
     * Set's the version values according to the ouput of a 'git describe --tags' command and a timestamp.
     * 
     * @param string $tag The git tag string, e.g. v1.3.10-rc-1-gabcdef
     * @param string $timestamp The timestamp of the commit associated with the tag.
     * @return void
     */
    public function setByGitTag(string $tag, string $timestamp){
        $parts = explode('-', $tag);
        $this->release = $parts[0];
        $this->releaseName = ucfirst($parts[1]);
        if(count($parts) >= 4) $this->releaseHash = $parts[3];
        // cut off 'v' for semantic versioning
        $semVer = explode('.', substr($this->release, 1));
        $this->major = intval($semVer[0]);
        $this->minor = intval($semVer[1]);
        $this->patch = intval($semVer[2]);

        $this->time = $timestamp;
    }
    
    public function fetchFromGit(){
        exec('git describe --tags', $tag, $exitcode);
        exec('git log -1 --format=%at', $timestamp, $exitcodeTs);
        if($exitcode === 0 && $exitcodeTs === 0) {
            $this->setByGitTag($tag[0], $timestamp[0]);
        } else {
            // TODO ! DANGER ! 
            // When the fetching of the version from git fails
            // it sets the version to the lowest possible state.
            // E.g. there is an update on an actual lower version,
            // this would go through and potentially cause serious issues. [SO]
            $this->major = '0';
            $this->minor = '0';
            $this->patch = '0';
            $this->release = 'v0.0.0';
            $this->releaseName = 'Unreleased';
            $this->releaseHash = null;
            $this->time = time();
            return;
        }
    }
    
    /**
     * Get's the release version with the version prefix.
     * @return string - The release version with the version prefix, e.g. v1.3.10
     */
    public function getRelease() {
        return $this->release;
    }
    
    /**
     * Get's the release version as string without prefix.
     * @return string - The release version as a string without the 'v' prefix, e.g. 1.3.10
     */
    public function getReleaseRaw() {
        return $this->major . '.' . $this->minor . '.' . $this->patch;
    }

    public function getReleaseName() {
        return $this->releaseName;
    }

    public function getReleaseHash(): ?string {
        return $this->releaseHash;
    }

    public function getReadableRelease() {
        return "$this->release ($this->releaseName)";
    }

    public function getFullRelease() {
        $releaseName = Str::lower($this->releaseName);
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
    
    public function toObject(): array {
        return [
            'full' => $this->getFullRelease(),
            'readable' => $this->getReadableRelease(),
            'release' => $this->getRelease(),
            'name' => $this->getReleaseName(),
            'time' => $this->getTime()
        ];
    }
}
