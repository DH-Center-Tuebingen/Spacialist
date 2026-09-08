<?php

namespace App\Traits;


trait HasDynamicDisk {
    private $disk = "private";
    
    public function setDisk(string $disk): void {
        $this->disk = $disk;
    }

    public function getDisk(): string {
        return $this->disk;
    }
}