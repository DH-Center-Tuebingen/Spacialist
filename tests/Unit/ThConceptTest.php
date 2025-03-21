<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\ThConcept;
use App\ThConceptLabel;
use Illuminate\Support\Facades\App;

class ThConceptTest extends TestCase
{
    /**
     * Test locale label of a concept (id=1)
     *
     * @return void
     */
    public function testLocaleLabel() {
        $concept = ThConcept::find(1);
        $labelEn = $concept->getActiveLocaleLabel();
        $this->assertEquals('Site', $labelEn);

        App::setLocale('de');
        $labelDe = $concept->getActiveLocaleLabel();
        $this->assertEquals('Fundstelle', $labelDe);

        App::setLocale('en');
        $this->assertEquals('Site', $labelEn);
    }

    /**
     * Test relations of a concept (id=20)
     *
     * @return void
     */
    public function testRelations()
    {
        $concept = ThConcept::with(['labels', 'narrowers', 'broaders'])->find(20);

        $this->assertEquals(1, $concept->labels->count());
        $this->assertEquals(26, $concept->labels[0]->id);
        $this->assertEquals(1, $concept->labels[0]->language_id);
        $this->assertEquals(20, $concept->labels[0]->concept_id);
        $this->assertEquals(0, $concept->narrowers->count());
        $this->assertEquals(1, $concept->broaders->count());
        $this->assertEquals(17, $concept->broaders[0]->id);
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
