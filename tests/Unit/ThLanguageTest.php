<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\ThLanguage;

class ThLanguageTest extends TestCase
{
    /**
     * Test relations of an language (id=2, EN)
     *
     * @return void
     */
    public function testRelations()
    {
        $l = ThLanguage::with(['labels'])->find(2);

        $this->assertEquals(5, $l->labels->count());
        $this->assertArraySubset([
            [
                'concept_id' => 1,
                'label' => 'Site',
            ],
            [
                'concept_id' => 2,
                'label' => 'Feature',
            ],
            [
                'concept_id' => 3,
                'label' => 'Find',
            ],
            [
                'concept_id' => 9,
                'label' => 'Pottery',
            ],
            [
                'concept_id' => 10,
                'label' => 'Stone',
            ],
        ], $l->labels->toArray());
    }

    /**
     * Test get last editor of first language
     *
     * @return void
     */
    public function testGetLanguageLasteditor()
    {
        $lang = ThLanguage::first();
        $this->assertEquals(1, $lang->user->id);
    }
}
