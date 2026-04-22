<?php
namespace Tests\Support\PluginTemplateFeatures;

use PhpParser\Node\Expr\Cast\String_;
use Tests\Support\PluginTemplateFeature;

class HookTemplateFeature extends PluginTemplateFeature {

    private $hooks = [];

    /**
     * Adds a plugin hook to the template. In production'src' and 'on' are required.
     * In testing all values are optional.
     * 
     * @param mixed $on - The hook point, e.g. 'api/v1/pre'
     * @param mixed $src - The php function for the hook, e.g. 'Hooks\\AddPreData@apply'
     * @param mixed $order - The order of the hook, lower numbers run first. Default is 0.
     * @return void
     */
    public function addHook(mixed $on = null, mixed $src = null, mixed $order = null): void {
        $hook = [];
        if($on !== null) {
            $hook['on'] = $on;
        }

        if($src !== null) {
            $hook['src'] = $src;
        }

        if($order !== null) {
            $hook['order'] = $order;
        }
        $this->hooks[] = $hook;
    }
    
    public function getName(): string
    {
        return 'hook';
    }

    public function generateXml(): string {
        if($this->hooks === null || count($this->hooks) === 0) {
            return "";
        }
        $hooksXml = "";
        foreach($this->hooks as $hook) {
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
}