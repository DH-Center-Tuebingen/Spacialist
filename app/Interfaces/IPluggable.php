<?php

namespace App\Interfaces;

use App\Plugin;

interface IPluggable {
    public function install(Plugin $plugin): void;
    public function update(Plugin $plugin): void;
    public function uninstall(Plugin $plugin): void;
    public function remove(Plugin $plugin): void;
}