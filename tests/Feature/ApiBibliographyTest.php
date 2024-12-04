<?php

namespace Tests\Feature;

use App\Bibliography;
use Illuminate\Http\UploadedFile;
use Tests\Permission;
use Tests\ResponseTester;
use Tests\TestCase;

class ApiBibliographyTest extends TestCase
{
    /**
     * Tests the get all (GET /bibliography/) and
     * add entry (POST /bibliography/) API endpoints
     * @return void
     */
     public function getBibliographyStructure(): array {
        return [
            // system fields
            'id',
            'entry_type',
            'file',
            'user_id',
            'created_at',
            'updated_at',
            // bibtex fields
            'address',
            'annote',
            'author',
            'booktitle',
            'chapter',
            'citekey',
            'crossref',
            'edition',
            'editor',
            'howpublished',
            'institution',
            'journal',
            'key',
            'misc',
            'month',
            'note',
            'number',
            'organization',
            'pages',
            'publisher',
            'school',
            'series',
            'title',
            'type',
            'volume',
            'year',
        ];
     }

     /**
      * @testdox GET /api/v1/bibliography/
      */
     public function testGetAll() {
        $response = $this->userRequest()
            ->get('/api/v1/bibliography');

        $this->assertStatus($response, 200);
        $response->assertJsonCount(6);
     }


    /**
    * @testdox GET /api/v1/bibliography/{id}
    */
     public function testGetSingle() {
        $response = $this->userRequest()
            ->get('/api/v1/bibliography/1320');

        $this->assertStatus($response, 200);
        $response->assertJsonStructure($this->getBibliographyStructure());
        $response->assertJson([
            'id' => 1320,
            'entry_type' => 'article',
            'title' => '{Finite diagrams stable in power}',
            'author' => 'Shelah, Saharon',
            'journal' => 'Annals of Mathematical Logic',
            'pages' => '69--118',
            'volume' => '2',
            'citekey' => 'Sh:3'
        ]);
     }

     /**
    * @testdox GET /api/v1/bibliography/{id}/ref_count
    */
    public function testGetReferenceCountEndpoint()
    {
        $response = $this->userRequest()
            ->get('/api/v1/bibliography/1319/ref_count');

        $this->assertStatus($response, 200);
        $response->assertSimilarJson([1]);
    }


    /**
     * @testdox POST /api/v1/bibliography/
     */
    public function testAdd() {
        $response = $this->userRequest()
            ->post('/api/v1/bibliography', [
                'entry_type' => 'article',
                'title' => 'Schweinegerichte und deren gesundheitliche Auswirkungen',
                'author' => 'Dietmar Köppke and Jürgen Sauer',
                'journal' => 'Kulinarik 101',
                'pages' => '10-15',
                'year' => '2021'
            ]);

        $this->assertStatus($response, 201);
        $response->assertJsonStructure($this->getBibliographyStructure());

        $entry_id = 1324;
        $response->assertJson([
            'id' => $entry_id,
            'entry_type' => 'article',
            'title' => 'Schweinegerichte und deren gesundheitliche Auswirkungen',
            'author' => 'Dietmar Köppke and Jürgen Sauer',
            'journal' => 'Kulinarik 101',
            'pages' => '10-15',
            'year' => '2021',
            'citekey' => 'Dietmar Köppke_Schweinegerichte_sudga_2021'
        ]);


        $response = $this->userRequest()
            ->get('/api/v1/bibliography');

        $this->assertStatus($response, 200);
        $response->assertJsonCount(7);

        $response = $this->userRequest()
            ->get("/api/v1/bibliography/$entry_id");

        $this->assertStatus($response, 200);
        $response->assertJsonStructure($this->getBibliographyStructure());
        $response->assertJson([
            'entry_type' => 'article',
            'title' => 'Schweinegerichte und deren gesundheitliche Auswirkungen',
            'author' => 'Dietmar Köppke and Jürgen Sauer',
            'journal' => 'Kulinarik 101',
            'pages' => '10-15',
            'year' => '2021',
            'citekey' => 'Dietmar Köppke_Schweinegerichte_sudga_2021'
        ]);
    }

    private function importTest($filename, $results) {
        $path = storage_path() . "/framework/testing/$filename";
        $file = new UploadedFile($path, $filename, 'application/x-bibtex', null, true);

        $response = $this->userRequest()
            ->post('/api/v1/bibliography/import', [
                'file' => $file
            ]);

        $inserted = $response->json();

        $this->assertStatus($response, 201);
        $response->assertJsonCount(16);
        $response->assertJsonStructure([ "*" => [
            "entry" => $this->getBibliographyStructure()]
        ]);

        $response->assertJson(array_map(function($e) {
            return [
                "entry" => $e,
                "added" => true
            ];
        }, $results));

        $cnt = Bibliography::count();
        $this->assertEquals(22, $cnt);

        $i = 0;
        foreach($results as $r) {
            $addedItem = $inserted[$i++];
            // Note: I don't like the reliance on the $addedItem["entry"]["id"] here.
            //       But Postgres does not rollback the sequence when a transaction is rolled back.
            //       Therefore all alternative also seem to be more complex than this.
            $response = $this->userRequest()
                ->get('/api/v1/bibliography/'. $addedItem["entry"]["id"]);

            $this->assertStatus($response, 200);
            $response->assertJsonStructure($this->getBibliographyStructure());
            $response->assertJson($r);
        }
    }

    /**
     * @testdox POST /api/v1/bibliography/import (mandatory fields)
     */
    public function testMandatoryImport()
    {
       $this->importTest('import_mandatory.bib', [
           [
               "entry_type" => "article",
               "citekey" => "Smith2021",
               "author" => "John Smith and Jane Doe and Alice Brown",
               "title" => "A Comprehensive Study on Testing",
               "journal" => "Journal of Testing",
               "year" => "2021"
           ],
           [
               "entry_type" => "book",
               "citekey" => "Doe2020",
               "author" => "Jane Doe and John Smith and Carol Green",
               "title" => "The Art of Testing",
               "publisher" => "Testing Publishers",
               "year" => "2020"
           ],
           [
               "entry_type" => "book",
               "citekey" => "EditorBook2022",
               "editor" => "Emily Editor and David Black and Eve Gray",
               "title" => "Edited Volume on Testing",
               "publisher" => "Testing Publishers",
               "year" => "2022"
           ],
           [
               "entry_type" => "booklet",
               "citekey" => "Booklet2022",
               "title" => "Testing Booklet"
           ],
           [
               "entry_type" => "conference",
               "citekey" => "White2018",
               "author" => "Bob White and Carol Green and David Black",
               "title" => "Conference on Testing Methods",
               "booktitle" => "Annual Testing Conference",
               "year" => "2018"
           ],
           [
               "entry_type" => "inbook",
               "citekey" => "Taylor2023",
               "author" => "Laura Taylor and John Smith and Emily Brown",
               "title" => "In-depth Testing Techniques",
               "chapter" => "5",
               "pages" => "123-145",
               "publisher" => "Advanced Testing Publishers",
               "year" => "2023"
           ],
           [
               "entry_type" => "inbook",
               "citekey" => "EditorInBook2023",
               "editor" => "Nancy Editor and Alice White and Bob Smith",
               "title" => "Advanced Testing Strategies",
               "chapter" => "7",
               "pages" => "200-220",
               "publisher" => "Expert Testing Publishers",
               "year" => "2023"
           ],
           [
               "entry_type" => "incollection",
               "citekey" => "Johnson2022",
               "author" => "Michael Johnson and Alice White and Bob Smith",
               "title" => "Chapter on Testing",
               "booktitle" => "Handbook of Testing",
               "publisher" => "Testing Publishers",
               "year" => "2022"
           ],
           [
               "entry_type" => "inproceedings",
               "citekey" => "Brown2019",
               "author" => "Alice Brown and David Green and Eve Gray",
               "title" => "Proceedings of the Testing Conference",
               "booktitle" => "International Conference on Testing",
               "year" => "2019"
           ],
           [
               "entry_type" => "manual",
               "citekey" => "Manual2014",
               "title" => "Testing Manual"
           ],
           [
               "entry_type" => "mastersthesis",
               "citekey" => "Black2016",
               "author" => "David Black and Jane White and Bob Smith",
               "title" => "Testing in Software Engineering",
               "school" => "Institute of Testing",
               "year" => "2016"
           ],
           [
               "entry_type" => "misc",
               "citekey" => "Misc2013",
               "note" => "Misc has no mandatory fields"
           ],
           [
               "entry_type" => "phdthesis",
               "citekey" => "Green2017",
               "author" => "Carol Green and John Doe and Alice Brown",
               "title" => "Advanced Testing Techniques",
               "school" => "University of Testing",
               "year" => "2017"
           ],
           [
               "entry_type" => "proceedings",
               "citekey" => "Proceedings2021",
               "title" => "Proceedings of the 2021 Testing Symposium",
               "year" => "2021"
           ],
           [
               "entry_type" => "techreport",
               "citekey" => "Gray2015",
               "author" => "Eve Gray and Bob Smith and Carol Green",
               "title" => "Testing Report 2015",
               "institution" => "Testing Institute",
               "year" => "2015"
           ],
           [
               "entry_type" => "unpublished",
               "citekey" => "Unpublished2023",
               "author" => "Frank Unpublished and Emily Editor and David Black",
               "title" => "Unpublished Testing Research",
               "note" => "Manuscript in preparation"
           ]
           ]);
    }

    /**
     * @testdox POST /api/v1/bibliography/import (with optional fields)
     */
    public function testOptionalImport() {
        $this->importTest("import_optional.bib", [
            [
                "entry_type" => "article",
                "citekey" => "Smith2021",
                "author" => "John Smith and Jane Doe and Alice Brown",
                "title" => "A Comprehensive Study on Testing",
                "journal" => "Journal of Testing",
                "year" => "2021",
                "volume" => "3",
                "number" => "2",
                "month" => "Apr",
                "note" => "A significant piece of work"
            ],
            [
                "entry_type" => "book",
                "citekey" => "Doe2020",
                "author" => "Jane Doe and John Smith and Carol Green",
                "title" => "The Art of Testing",
                "publisher" => "Testing Publishers",
                "year" => "2020",
                "series" => "In-depth Series",
                "address" => "Boston",
                "edition" => "Third",
                "month" => "May",
                "note" => "Great book"
            ],
            [
                "entry_type" => "book",
                "citekey" => "EditorBook2022",
                "editor" => "Emily Editor and David Black and Eve Gray",
                "title" => "Edited Volume on Testing",
                "publisher" => "Testing Publishers",
                "year" => "2022",
                "series" => "Edited Series",
                "address" => "New York",
                "month" => "Oct",
                "note" => "Edited volume"
            ],
            [
                "entry_type" => "booklet",
                "citekey" => "Booklet2022",
                "title" => "Testing Booklet",
                "author" => "Richard Roe",
                "howpublished" => "Website",
                "address" => "Heidelberg, Germany",
                "month" => "Jun",
                "year" => "2022",
                "note" => "A booklet on testing"
            ],
            [
                "entry_type" => "conference",
                "citekey" => "White2018",
                "author" => "Bob White and Carol Green and David Black",
                "title" => "Conference on Testing Methods",
                "booktitle" => "Annual Testing Conference",
                "year" => "2018",
                "volume" => "6",
                "series" => "Workshop Series",
                "pages" => "100-120",
                "address" => "London, UK",
                "month" => "Dec",
                "organization" => "Testing Society",
                "publisher" => "Testing Publishers",
                "note" => "Conference paper"
            ],
            [
                "entry_type" => "inbook",
                "citekey" => "Taylor2023",
                "author" => "Laura Taylor and John Smith and Emily Brown",
                "title" => "In-depth Testing Techniques",
                "chapter" => "5",
                "pages" => "123-145",
                "publisher" => "Advanced Testing Publishers",
                "year" => "2023",
                "volume" => "4",
                "series" => "In-depth Series",
                "type" => "Technical",
                "address" => "Boston",
                "edition" => "3rd",
                "month" => "Jun",
                "note" => "A detailed chapter"
            ],
            [
                "entry_type" => "inbook",
                "citekey" => "EditorInBook2023",
                "author" => "Nancy Editor and Alice White and Bob Smith",
                "title" => "Advanced Testing Strategies",
                "chapter" => "7",
                "pages" => "200-220",
                "publisher" => "Expert Testing Publishers",
                "year" => "2023",
                "volume" => "5",
                "series" => "Strategies Series",
                "type" => "Research",
                "address" => "Seattle",
                "edition" => "First",
                "month" => "Jul",
                "note" => "An insightful chapter"
            ],
            [
                "entry_type" => "incollection",
                "citekey" => "Johnson2022",
                "author" => "Michael Johnson and Alice White and Bob Smith",
                "title" => "Chapter on Testing",
                "booktitle" => "Handbook of Testing",
                "publisher" => "Testing Publishers",
                "year" => "2022",
                "editor" => "Nancy Editor",
                "volume" => "3",
                "series" => "Testing Handbook Series",
                "type" => "Research",
                "chapter" => "10",
                "pages" => "150-170",
                "address" => "Atlanta",
                "edition" => "Fourth",
                "month" => "Mar",
                "note" => "A key chapter"
            ],
            [
                "entry_type" => "inproceedings",
                "citekey" => "Brown2019",
                "author" => "Alice Brown and David Green and Eve Gray",
                "title" => "Proceedings of the Testing Conference",
                "booktitle" => "International Conference on Testing",
                "year" => "2019",
                "editor" => "Michael Johnson",
                "volume" => "6",
                "series" => "Conference Proceedings",
                "pages" => "300-320",
                "address" => "New York",
                "month" => "Aug",
                "organization" => "Testing Society",
                "publisher" => "Proceedings Publishers",
                "note" => "A notable conference paper"
            ],
            [
                "entry_type" => "manual",
                "citekey" => "Manual2014",
                "title" => "Testing Manual",
                "author" => "John Smith",
                "organization" => "Testing Organization",
                "address" => "Los Angeles",
                "edition" => "1st",
                "month" => "Sep",
                "year" => "2014",
                "note" => "A comprehensive manual"
            ],
            [
                "entry_type" => "mastersthesis",
                "citekey" => "Black2016",
                "author" => "David Black and Jane White and Bob Smith",
                "title" => "Testing in Software Engineering",
                "school" => "Institute of Testing",
                "year" => "2016",
                "type" => "Master's Thesis",
                "address" => "Boston",
                "month" => "Oct",
                "note" => "A significant thesis"
            ],
            [
                "entry_type" => "misc",
                "citekey" => "Misc2013",
                "title" => "Miscellaneous Testing",
                "author" => "John Doe",
                "howpublished" => "Unpublished",
                "month" => "Nov",
                "year" => "2013",
                "note" => "Misc has no mandatory fields"
            ],
            [
                "entry_type" => "phdthesis",
                "citekey" => "Green2017",
                "author" => "Carol Green and John Doe and Alice Brown",
                "title" => "Advanced Testing Techniques",
                "school" => "University of Testing",
                "year" => "2017",
                "type" => "PhD Thesis",
                "address" => "San Francisco",
                "month" => "Dec",
                "note" => "A groundbreaking thesis"
            ],
            [
                "entry_type" => "proceedings",
                "citekey" => "Proceedings2021",
                "title" => "Proceedings of the 2021 Testing Symposium",
                "year" => "2021",
                "editor" => "Michael Johnson and Alice White",
                "volume" => "7",
                "series" => "Symposium Series",
                "address" => "Chicago, USA",
                "month" => "Nov",
                "organization" => "Testing Society",
                "publisher" => "Symposium Publishers",
                "note" => "A significant symposium"
            ],
            [
                "entry_type" => "techreport",
                "citekey" => "Gray2015",
                "author" => "Eve Gray and Bob Smith and Carol Green",
                "title" => "Testing Report 2015",
                "institution" => "Testing Institute",
                "year" => "2015",
                "type" => "Technical Report",
                "number" => "TR-2015-01",
                "address" => "Chicago",
                "month" => "Jan",
                "note" => "A detailed technical report"
            ],
            [
                "entry_type" => "unpublished",
                "citekey" => "Unpublished2023",
                "author" => "Frank Unpublished and Emily Editor and David Black",
                "title" => "Unpublished Testing Research",
                "note" => "Manuscript in preparation",
                "month" => "Feb",
                "year" => "2023"
            ]
            ]);
    }

    /**
     * @testdox POST /api/v1/bibliography/import (with invalid data)
     */
    public function testInvalidImport() {
        $name = 'import_wrong_structure.bib';
        $path = storage_path() . "/framework/testing/$name";
        $file = new UploadedFile($path, $name, 'application/x-bibtex', null, true);

        $response = $this->userRequest()
            ->post('/api/v1/bibliography/import', [
                'file' => $file
            ]);

        $this->assertStatus($response, 400);
        $response->assertSimilarJson([
            'error' => "Unexpected character '\\0' at line 10 column 1"
        ]);
    }


    /**
     * @testdox POST /api/v1/bibliography/export
     */
    function testExport() {
        $response = $this->userRequest()
                ->post('/api/v1/bibliography/export');

        $this->assertStatus($response, 200);
        $this->assertTrue($response->headers->get('content-type') == 'application/x-bibtex');
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=export.bib');
        $content = $this->getStreamedContent($response);
        $expectedContent = file_get_contents(storage_path() . "/framework/testing/demo.bib");
        $this->assertSame($expectedContent, $content);
    }

    function getUpdateData() {
        return [
            'entry_type'        => 'book',
            'author'            => 'Köppke, Dietmar and Sauer, Jürgen',
            'editor'            => 'King, Robert and Smith, John',
            'title'             => 'Patched Title',
            'journal'           => 'Patched Journal',
            'year'              => 'Patched Year',
            'pages'             => 'Patched Pages',
            'volume'            => 'Patched Volume',
            'number'            => 'Patched Number',
            'booktitle'         => 'Patched Booktitle',
            'publisher'         => 'Patched Publisher',
            'address'           => 'Patched Address',
            'misc'              => 'Patched Misc',
            'howpublished'      => 'Patched Howpublished',
            'annote'            => 'Patched Annote',
            'chapter'           => 'Patched Chapter',
            'crossref'          => 'Patched Crossref',
            'edition'           => 'Patched Edition',
            'institution'       => 'Patched Institution',
            'key'               => 'Patched Key',
            'month'             => 'Patched Month',
            'note'              => 'Patched Note',
            'organization'      => 'Patched Organization',
            'school'            => 'Patched School',
            'series'            => 'Patched Series',
            'type'              => 'Patched Type',
            'abstract'          => 'Patched Abstract',
            'doi'               => 'Patched Doi',
            'isbn'              => 'Patched Isbn',
            'issn'              => 'Patched Issn',
            'language'          => 'Patched Language',
        ];
    }


    /*
     * TODO: The current problem here is that we are not simply updating
     *       all table cells according to the passed data but instead sanatizing the
     *       data and only updating the fields that fit the entry_type.
     *
     *      All other fields are set to NULL. This is at the time of writing the
     *      desired behavior. For the tests there is the problem, that we need to
     *      test every entry type. This is not yet implemented.
     *
     *      [SO]
     */

     /**
      * @testdox POST /api/v1/bibliography/{id}
      */
     function testPatchItem() {
        $data = $this->getUpdateData();
        $response = $this->userRequest()
            ->post('/api/v1/bibliography/1320', $data);

        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            'id',
            'type',
            'citekey',
            'title',
            'author',
            'editor',
            'journal',
            'year',
            'pages',
            'volume',
            'number',
            'booktitle',
            'publisher',
            'address',
            'misc',
            'howpublished',
            'annote',
            'chapter',
            'crossref',
            'edition',
            'institution',
            'key',
            'month',
            'note',
            'organization',
            'school',
            'series',
            'user_id',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson(array_merge($data, [
          // Set all non-book fields to NULL
            'annote'            => null,
            'booktitle'         => null,
            'chapter'           => null,
            'crossref'          => null,
            'howpublished'      => null,
            'institution'       => null,
            'issn'              => null,
            'journal'           => null,
            'key'               => null,
            'misc'              => null,
            'number'            => null,
            'organization'      => null,
            'pages'             => null,
            'school'            => null,
            'type'              => null,
            'volume'            => null,
        ]));
     }
     
         
    function testSinglePatchItem(){
        $response = $this->userRequest()
            ->post('/api/v1/bibliography/1318', [
                'entry_type' => 'article',
                'title' => 'Patched Single Field'
            ]);
            
        $response->assertStatus(200);
        $response->assertJson([
            'id' => 1318,
            'entry_type' => 'article',
            // Citekey gets generated on every change. 
            //'citekey' => 'Sh:1',
            'title' => 'Patched Single Field',
            'author' => 'Shelah, Saharon',
            'editor' => NULL,
            'journal' => 'Israel Journal of Mathematics',
            'year' => '1969',
            'pages' => '187--202',
            'volume' => '7'
        ]);
    }
    
    function testSinglePatchChangeType(){
        $response = $this->userRequest()
            ->post('/api/v1/bibliography/1318', [
                'entry_type' => 'book',
                'title' => 'Patched To Book',
                'publisher' => 'Patched Publisher'
            ]);
            
        $response->assertStatus(200);
        $response->assertJson(array_merge([
            'id' => 1318,
            'entry_type' => 'book',
            // Citekey gets generated on every change. 
            //'citekey' => 'Sh:1',
            'title' => 'Patched To Book',
            'author' => 'Shelah, Saharon',
            'editor' => NULL,
            'year' => '1969',
            'publisher' => 'Patched Publisher',
        ],[
          // Set all non-book fields to NULL
            'annote'            => null,
            'booktitle'         => null,
            'chapter'           => null,
            'crossref'          => null,
            'howpublished'      => null,
            'institution'       => null,
            'issn'              => null,
            'journal'           => null,
            'key'               => null,
            'misc'              => null,
            'number'            => null,
            'organization'      => null,
            'pages'             => null,
            'school'            => null,
            'type'              => null,
            'volume'            => null,
        ]));
    }


     /**
      * @testdox DELETE /api/v1/bibliography/{id}
      */
     public function testDelete() {
        $bib = Bibliography::latest()->first();

        $response = $this->userRequest()
            ->delete('/api/v1/bibliography/'.$bib->id);

        $this->assertStatus($response, 204);
        $cnt = Bibliography::count();
        $this->assertEquals(5, $cnt);
        $this->assertEquals("", $response->getContent());
    }

    /// TODO: Add tests for the following endpoints:
    // - DELETE /{id}/file
    // - UPLOAD file in all requests

    /**
     * @dataProvider permissions
     */
    public function testWithoutPermission($permission) {
        (new ResponseTester($this))->testMissingPermission($permission);
    }
    /**
     * @dataProvider exceptionPermissions
     */
    public function testSucceedWithPermission($permission) {
        (new ResponseTester($this))->testExceptions($permission);
    }

    // TODO: We should test the success of each endpoint by using a user that has only the single required permission. [SO]
    public static function permissions() {
        return [
            'permission to add item'    => Permission::for("post", '/api/v1/bibliography' ,'You do not have the permission to add new bibliography'),
            'permission to update item' => Permission::for("post", '/api/v1/bibliography/9000', 'You do not have the permission to edit existing bibliography'),
            'permission to delete item' => Permission::for("delete", '/api/v1/bibliography/9000', 'You do not have the permission to remove bibliography entries'),
            'permission to import item' => Permission::for("post", '/api/v1/bibliography/import', 'You do not have the permission to add new/modify existing bibliography items'),
        ];
    }

    public static function exceptionPermissions() {
        return [
            "missing ref count" => Permission::for("get", '/api/v1/bibliography/99/ref_count', 'This bibliography item does not exist'),
            "update missing item" => Permission::for("post", '/api/v1/bibliography/99', 'This bibliography item does not exist'),
            "delete missing item" => Permission::for("delete", '/api/v1/bibliography/99', 'This bibliography item does not exist'),
        ];
    }
}
