<?php

namespace Tests\Support;

use Tests\Support\PluginTemplate;

class PluginXml {
    public function __construct(protected PluginTemplate $template) {
    }

    public static function generate(PluginTemplate $template): string {
        $pluginXML = new static($template);
        return $pluginXML->generateInfoXml();
    }


    public function generateInfoXml(): string {
        $plugin = $this->template->plugin;
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence = isset($plugin['licence']) ? $plugin['licence'] : "";


        $hooksText = $this->generateHooks();
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
        {$hooksText}
    </info>
    XML;
    }
    private function generateHooks(): string {
        $hooks = $this->template->getHooks();
        if($hooks === null || count($hooks) === 0) {
            return "";
        }
        $hooksXml = "";
        foreach($hooks as $hook) {
            $hooksXml .= "    <hook";
            if(isset($hook['on'])) {
                $hooksXml .= " on=\"{$hook['on']}\"";
            }
            if(isset($hook['src'])) {
                $hooksXml .= " src=\"{$hook['src']}\"";
            }
            if(isset($hook['order'])) {
                $hooksXml .= " order=\"{$hook['order']}\"";
            }
            $hooksXml .= " />\n";
        }
        return "<hooks>\n" . $hooksXml . "</hooks>";
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