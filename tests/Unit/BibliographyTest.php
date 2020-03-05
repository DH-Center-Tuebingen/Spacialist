<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

use App\Bibliography;

class BibliographyTest extends TestCase
{
    /**
     * Test relations of an bibliography entry (id=1318)
     *
     * @return void
     */
    public function testRelations()
    {
        $b = Bibliography::with(['entities'])->find(1318);

        $this->assertEquals(1, $b->entities[0]->id);
        $this->assertEquals(15, $b->entities[0]->pivot->attribute_id);
        $this->assertEquals('See Page 10', $b->entities[0]->pivot->description);
    }

    /**
     * Test
     *
     * @return void
     */
    public function testParsingRequest()
    {
        $r = new Request();
        $r->replace([
            'year' => 1999,
            'type' => 'book',
        ]);

        $user = new \stdClass;
        $user->id = 1;

        $ranges = array_merge([''], range('a', 'z'), range('A', 'Z'));
        foreach($ranges as $letter) {
            $b = new Bibliography();
            $b->title = 'Title';
            $b->type = 'article';
            $b->citekey = 'No:1999'.$letter;
            $b->user_id = 1;
            $b->save();
        }

        $bib = new Bibliography();
        $bib->fieldsFromRequest($r, $user);

        $newBib = Bibliography::orderBy('id', 'desc')->first();
        $this->assertEquals($bib->id, $newBib->id);
        $this->assertEquals('No Title', $newBib->title);
        $this->assertEquals('No:1999aa', $newBib->citekey);
    }
}
