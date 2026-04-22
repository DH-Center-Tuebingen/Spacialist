<?php
namespace Tests\Support\PluginTemplateFeatures;

use PhpParser\Node\Expr\Cast\String_;
use Tests\Support\PluginTemplateFeature;

class CssTemplateFeature extends PluginTemplateFeature {

    private $files = [];

    public function addFile(string $src): void {
        $this->files[] = ["src" => $src];
    }

    public function getId(): string {
        return "css";
    }

    public function getName(): string
    {
        return 'css';
    }

    public function generateXml(): string {
        if($this->files === null || count($this->files) === 0) {
            return "";
        }
        $cssXml = "";
        foreach($this->files as $file) {
            $cssXml .= "    <file src=\"{$file['src']}\"";
            
            $cssXml .= " />\n";
        }
        return "<css>\n" . $cssXml . "</css>";
    }
}