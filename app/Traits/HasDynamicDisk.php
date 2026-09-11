<?php

namespace App\Traits;

/**
 * Implements a $disk variable that allows the consumer to change the disk for
 * special purposes.
 * 
 * Exposes:
 * + getDisk(): string            - Disk getter function.
 * + setDisk(string $disc): void  - Sets the disk.
 * 
 * 
 * 
 * Note: Primarily used to set a fake store inside tests.
 */
trait HasDynamicDisk {
    private $disk = "private";
    
    public function setDisk(string $disk): void {
        $this->disk = $disk;
    }

    public function getDisk(): string {
        return $this->disk;
    }
}