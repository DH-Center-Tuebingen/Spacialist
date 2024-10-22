<?php

namespace Tests\Unit;

use Tests\TestCase;

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
            'entry_type' => 'book',
            'author' => 'Book Author',
            'publisher' => 'Spacialist',
            'title' => 'Book Title',
        ]);

        $user = new \stdClass;
        $user->id = 1;

        $ranges = array_merge([''], range('a', 'z'), range('A', 'Z'));
        foreach($ranges as $letter) {
            $b = new Bibliography();
            $b->title = 'Title';
            $b->entry_type = 'article';
            $b->citekey = 'Book Author_Book_bt_1999'.$letter;
            $b->user_id = 1;
            $b->save();
        }

        $bib = new Bibliography();
        $bib->fieldsFromRequest($r, $user);

        $newBib = Bibliography::orderBy('id', 'desc')->first();
        $this->assertEquals($bib->id, $newBib->id);
        $this->assertEquals('Book Title', $newBib->title);
        $this->assertEquals('Book Author_Book_bt_1999aa', $newBib->citekey);
    }

    /**
     * Test get last editor of first bibliography entry
     *
     * @return void
     */
    public function testGetBibliographyEntryLasteditor()
    {
        $entry = Bibliography::first();
        $this->assertEquals(1, $entry->user->id);
    }
}
