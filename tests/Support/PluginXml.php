<?php

namespace Tests\Support;

use Tests\Support\PluginTemplate;

class PluginXml {
    public function __construct(protected PluginTemplate $template) {
    }

    public static function generate(PluginTemplate $template, string $additionalXML): string {
        $pluginXML = new static($template);
        return $pluginXML->generateInfoXml($additionalXML);
    }


    public function generateInfoXml($additionalXML): string {
        $plugin = $this->template->plugin;
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence = isset($plugin['licence']) ? $plugin['licence'] : "";
        $authorsText = $this->generateAuthors();

        return <<<XML
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <info>
        <!-- Must match id in JS SpPS.register call in kebab-case -->
        <name>{$plugin['name']}</name>
        <title>{$plugin['name']} Plugin</title>
        <description>{$description}</description>
        <version>{$plugin['version']}</version>
        <license>{$licence}</license>
        {$authorsText}
        {$additionalXML}
    </info>
    XML;
    }
    protected function generateAuthors(): string {
        $authors = $this->template->plugin['authors'] ?? [];
        if(count($authors) === 0) {
            return "";
        }
        $authorsXml = "";
        foreach($authors as $author) {
            $authorsXml .= "    <author>{$author}</author>\n";
        }
        return "<authors>\n" . $authorsXml . "</authors>";
    }

}