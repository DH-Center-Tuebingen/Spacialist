<?php

return [
    'custom' => [],
    'geom_type' => ':attribute must be one of '.implode(", ", \App\Geodata::availableGeometryTypes),
];
