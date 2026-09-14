<?php

namespace Tests\Support;

abstract class PluginTemplateFeature {
    abstract public function generateXml(): string;
    abstract public function getName(): string;
}