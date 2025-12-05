<?php

use App\AttributeTypes\AttributeBase;

test('parse export', function () {
    $exportValue = AttributeBase::parseExport(1);
    expect($exportValue)->toEqual('1');
});
