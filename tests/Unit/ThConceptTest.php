<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\ThConcept;
use App\ThConceptLabel;

class ThConceptTest extends TestCase
{
    /**
     * Test relations of a concept (id=20)
     *
     * @return void
     */
    public function testRelations()
    {
        $concept = ThConcept::with(['labels', 'narrowers', 'broaders', 'files'])->find(20);

        $this->assertEquals(1, $concept->labels->count());
        $this->assertEquals(26, $concept->labels[0]->id);
        $this->assertEquals(1, $concept->labels[0]->language_id);
        $this->assertEquals(20, $concept->labels[0]->concept_id);
        $this->assertEquals(0, $concept->narrowers->count());
        $this->assertEquals(1, $concept->broaders->count());
        $this->assertEquals(17, $concept->broaders[0]->id);
        $this->assertEquals(1, $concept->files->count());
        $this->assertEquals(5, $concept->files[0]->id);
        $this->assertEquals('test_img_edin.jpg', $concept->files[0]->name);
    }

    /**
     * Test get last editor of first thesaurus concept
     *
     * @return void
     */
    public function testGetThConceptLasteditor()
    {
        $concept = ThConcept::first();
        $this->assertEquals(1, $concept->user->id);
        $label = ThConceptLabel::first();
        $this->assertEquals(1, $label->user->id);
    }
}
