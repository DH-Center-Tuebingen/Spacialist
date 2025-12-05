<?php

test('column names helper', function () {
    $attributeColumns = sp_column_names('attributes');
    expect($attributeColumns)->toEqual([
        'id',
        'thesaurus_url',
        'datatype',
        'text',
        'thesaurus_root_url',
        'parent_id',
        'created_at',
        'updated_at',
        'recursive',
        'root_attribute_id',
        'is_system',
        'multiple',
        'restrictions',
        'metadata',
    ]);

    $eaColumns = sp_column_names('entity_attributes');
    expect($eaColumns)->toEqual([
        'id',
        'entity_type_id',
        'attribute_id',
        'position',
        'depends_on',
        'created_at',
        'updated_at',
        'metadata',
    ]);
});
