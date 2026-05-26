<?php

namespace Tests\Support;

use Tests\Support\PluginTemplate;

/**
 * Generates the plugin.xml for a plugin template
 */
class PluginXml {
    public function __construct(protected PluginTemplate $template) {
    }

    public static function generate(PluginTemplate $template): string {
        $pluginXML = new static($template);
        return $pluginXML->generateInfoXml();
    }
    
    private function buildTemplateXml(){
        $xml = "";
        foreach($this->template->getXml() as $xmlData) {
            $xml .= $this->buildXml($xmlData['rootTag'], $xmlData['childTag'], $xmlData['content']);
        }
        return $xml;
    }

    private function buildXml(string $rootTag, string $childTag, array $content): string {
        $xmlContent = "    <$rootTag>\n";
        foreach($content as $key => $value) {
            $xmlContent .= "        <$childTag ";
            foreach($value as $attributeKey => $attributeValue) {
                $xmlContent .= "$attributeKey=\"$attributeValue\" ";
            }
            $xmlContent .= " />\n";
        }
        $xmlContent .= "    </$rootTag>";
        return $xmlContent;
    }


    public function generateInfoXml(): string {
        $plugin = $this->template->plugin;
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence = isset($plugin['licence']) ? $plugin['licence'] : "";
        $authorsText = $this->generateAuthors();
        $additionalXml = $this->buildTemplateXml();
        
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
        {$additionalXml}
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