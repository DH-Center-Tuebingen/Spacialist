<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Bibliography;

use Illuminate\Http\UploadedFile;

class ApiBibliographyTest extends TestCase
{
    /**
     * Tests the get all (GET /bibliography/) and
     * add entry (POST /bibliography/) API endpoints
     * @return void
     */
    public function testGetAllAndAddEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/bibliography');

        $response->assertStatus(200);
        $response->assertJsonCount(61);
        $this->refreshToken($response);

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/bibliography', [
                'type' => 'article',
                'title' => 'Test Article',
                'author' => 'PhpUnit',
                'pages' => '10-15'
            ]);

        $response->assertStatus(201);
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
            'lasteditor',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson([
            'type' => 'article',
            'title' => 'Test Article',
            'author' => 'PhpUnit',
            'citekey' => 'Ph:0000',
            'year' => null,
            'pages' => '10-15'
        ]);
        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/bibliography');

        $response->assertStatus(200);
        $response->assertJsonCount(62);
    }

    /**
     * Test getting count of references for a bibliography entry (id=1319)
     * @return void
     */
    public function testGetReferenceCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/bibliography/1319/ref_count');

        $response->assertStatus(200);
        $response->assertExactJson([1]);
    }

    /**
     * Test importing a bibtex file
     * @return void
     */
    public function testImportBibtexEndpoint()
    {
        $cnt = Bibliography::count();
        $this->assertEquals(61, $cnt);

        $name = 'import.bib';
        $path = storage_path() . "/framework/testing/$name";
        $file = new UploadedFile($path, $name, 'application/x-bibtex', null, true);

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/bibliography/import', [
                'file' => $file
            ]);

        // dd($response);

        $response->assertStatus(201);
        $response->assertJsonCount(2);
        $response->assertJsonStructure([
            [
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
                'lasteditor',
                'created_at',
                'updated_at',
            ]
        ]);
        $response->assertJson([
            [
                'type' => 'book',
                'citekey' => 'Te:1984',
                'author' => 'Test Author',
                'journal' => null,
                'pages' => '1--3',
                'title' => 'Test Booktitle',
                'booktitle' => 'Test Book I',
                'volume' => '3',
                'year' => '1984',
            ],
            [
                'type' => 'article',
                'citekey' => 'Te:1337',
                'author' => 'Test Author',
                'journal' => 'Test Journal',
                'pages' => '13--37',
                'title' => 'Test Title',
                'volume' => '1',
                'year' => '1337',
                'institution' => null,
            ],
        ]);
        $cnt = Bibliography::count();
        $this->assertEquals(63, $cnt);
    }

    /**
     * Tests the add (POST /bibliography/),
     * export all (GET /bibliography/export/),
     * patch item (PATCH /bibliography/{id}) and
     * delete (DELETE /bibliography/{id}) API endpoints
     * @return void
     */
    public function testAddExportPatchAndDeleteEndpoint()
    {
        $cnt = Bibliography::count();
        $this->assertEquals(61, $cnt);

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->post('/api/v1/bibliography', [
                'type' => 'article',
                'title' => 'Test Article',
                'author' => 'PhpUnit',
                'pages' => '10-15'
            ]);

        $response->assertStatus(201);
        $cnt = Bibliography::count();
        $this->assertEquals(62, $cnt);

        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/bibliography/export');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-type') == 'application/x-bibtex');
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=export.bib');
        $content = $this->getStreamedContent($response);
        $this->assertContains("@article{Ph:0000,\n    title: {Test Article}\n    author: {PhpUnit}\n    pages: {10-15}\n}\n\n", $content);

        $this->refreshToken($response);
        $bib = Bibliography::latest()->first();
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->patch('/api/v1/bibliography/'.$bib->id, [
                'type' => 'book',
                'title' => 'Patched Title',
                'institution' => 'University of Tuebingen',
                'year' => '2019',
                'pages' => ''
            ]);

        $response->assertStatus(200);
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
            'lasteditor',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson([
            'type' => 'book',
            'title' => 'Patched Title',
            'author' => 'PhpUnit',
            'institution' => 'University of Tuebingen',
            'citekey' => 'Ph:2019',
            'year' => '2019',
            'pages' => ''
        ]);

        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/bibliography/'.$bib->id);

        $response->assertStatus(204);
        $cnt = Bibliography::count();
        $this->assertEquals(61, $cnt);
        $this->assertEquals("", $response->getContent());
    }
}
