<?php

namespace Tests\Support;

use Tests\Support\PluginTemplate;

/**
 * Generates the plugin.xml for a plugin template
 */
class PluginXml {
    public function __construct(protected PluginTemplate $template) {
    }

    /**
     * Generates the info xml for the template.
     * @param PluginTemplate $template - The plugin template for which to generate the info XML.
     * @return string - String representation of the generated info XML.
     */
    public static function generate(PluginTemplate $template): string {
        $pluginXML = new static($template);
        return $pluginXML->generateInfoXml();
    }

    /**
     * Retrieves the xml from the template and generates 
     * the string representation.
     * @return string String representation of the template xml.
     */
    private function buildTemplateXml() {
        $xml = "";
        foreach($this->template->getXml() as $xmlData) {
            $xml .= $this->buildXml($xmlData['rootTag'], $xmlData['childTag'], $xmlData['content']);
        }
        return $xml;
    }

    /**
     * Generates the XML content.
     * 
     * Normally uses a the provided root tag and generates child nodes according to the attributes provided
     * inside the content array. If child nodes are set to null, the root tags will be generated according to
     * the provided content.
     * 
     * @param string $rootTag - Root tag name. 
     * @param string|null $childTag - Child tag name. 
     * @param array $content - Array of associative array which define the attribute key/value pairs. This will be used to generate the tags. E.g. if count of content is 6, 5 child nodes will be generated.
     * @return string XML string representation of the provided values.
     */
    private function buildXml(string $rootTag, string | null $childTag, array $content): string {
        if($childTag) {
            return $this->buildXMLChildMode($rootTag, $childTag, $content);
        } else {
            return $this->buildXMLParentMode($rootTag, $content);
        }
    }

    /**
     * Creates the xml content string using the $rootTag as outer tag, while adding $childTags with the attribute key-value paris defined in $content.
     * 
     * NOTE: The $childTag can be overwritten by setting the '_tag' key in the $content array.
     * @param string $rootTag The root XML tag.
     * @param string $childTag The child XML tag.
     * @param array $content The array of attributes for the child tag.
     * @return string The generated XML content for the child mode.
     */
    private function buildXMLChildMode(string $rootTag, string $childTag, array $content): string {
        $xmlContent = "    <$rootTag>\n";

        foreach($content as $key => $value) {
            $tag = $value['_tag'] ?? $childTag;
            unset($value['_tag']);
            $xmlContent .= "        <$tag ";
            $xmlContent .= $this->buildAttributes($value);
            $xmlContent .= " />\n";
        }
        $xmlContent .= "    </$rootTag>";
        return $xmlContent;
    }
    
    /**
     * Generates a one or multiple tags and sets their attributes to $content.
     * @param string $rootTag The root XML tag.
     * @param array $content The array of attributes for the root tag.
     * @return string The generated XML content for the parameters.
     */
    private function buildXMLParentMode(string $rootTag, array $content): string {
        $xmlContent = "";
        foreach($content as $key => $value) {
            $xmlContent .= "    <$rootTag ";
            $xmlContent .= $this->buildAttributes($value);
            $xmlContent .= " />\n";
        }
        return $xmlContent;
    }
    
    /**
     * Generates the tag attributes. If a value is null it will be skipped.
     * If you need to use a null value, use the string 'null'.
     * @param array $attributes - An associative array of attribute names and values for the XML tag.
     * @return string The generated string of XML tag attributes.
     */
    private function buildAttributes(array $attributes): string {
        $attrString = "";
        foreach($attributes as $key => $value) {
            if($value !== null){ 
                $attrString .= "$key=\"$value\" ";
            }
        }
        return trim($attrString);
    }
    

    /**
     * Generates a plugin xml file based on the template.
     * 
     * @return string
     */
    public function generateInfoXml(): string {
        $plugin = $this->template->plugin;
        $description = isset($plugin['description']) ? $plugin['description'] : "";
        $licence = isset($plugin['licence']) ? $plugin['licence'] : "";
        $authorsText = $this->generateAuthors();
        $additionalXml = $this->buildTemplateXml();

        return <<<XML
    <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <plugin>
        <!-- Must match id in JS SpPS.register call in kebab-case -->
        <name>{$plugin['name']}</name>
        <title>{$plugin['name']} Plugin</title>
        <description>{$description}</description>
        <version>{$plugin['version']}</version>
        <license>{$licence}</license>
        {$authorsText}
        {$additionalXml}
    </plugin>
    XML;
    }
    
    /**
     * Generates the authors list from the template.
     * @return string The generated XML string for the authors list.
     */
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