<?php

namespace Tests\Support\Templates;

use Tests\Support\PluginTemplate;

class ScopeTemplate extends PluginTemplate {

    public function addEntityScope() {
        $this->addXml("scopes", "scope", [
            ["src" => "Scopes/EntityScope", "on" => "App\Entity"]
        ]);
        $this->addFile(
            "Scopes/EntityScope.php",
            <<<PHP
<?php

namespace App\Plugins\ScopePlugin\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EntityScope implements Scope {
    public function apply(Builder \$builder, Model \$model) {
        \$builder->where('entity_type_id', 3);
    }
}
PHP);

        return $this;
    }
}