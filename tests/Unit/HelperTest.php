<?php

namespace Tests\Unit;

use Tests\TestCase;


class HelperTest extends TestCase
{
    /**
     * Test sp_column_names helper
     *
     * @return void
     */
    public function testColumnNamesHelper()
    {
        $attributeColumns = sp_column_names('attributes');
        $this->assertEquals([
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
        ], $attributeColumns);

        $eaColumns = sp_column_names('entity_attributes');
        $this->assertEquals([
            'id',
            'entity_type_id',
            'attribute_id',
            'position',
            'depends_on',
            'created_at',
            'updated_at',
            'metadata',
        ], $eaColumns);
    }
}
