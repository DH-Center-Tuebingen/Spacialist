<?php

return [
    'custom' => [],
    'geom_type' => ':attribute must be one of '.implode(", ", \App\Geodata::availableGeometryTypes),
    'color' => ':attribute must be a hexadecimal color code',
];
