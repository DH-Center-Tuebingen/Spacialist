<?php

namespace App\AttributeTypes\Units\Unit;

use App\AttributeTypes\Units\Call;

class BaseUnit extends Unit {

    public function __construct(string $label, string $symbol) {
        parent::__construct($label, $symbol, Call::identity());
    }
}
