<?php

namespace Tests\Feature;

use Tests\TestCase;

use App\Bibliography;

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
        $response->assertJsonCount(0);
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
        $response->assertJsonCount(1);
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
        $this->assertEquals(0, $cnt);

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
        $this->assertEquals(1, $cnt);

        $this->refreshToken($response);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/bibliography/export');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->get('content-type') == 'application/x-bibtex');
        $this->assertTrue($response->headers->get('content-disposition') == 'attachment; filename=export.bib');
        $content = $this->getStreamedContent($response);
        $this->assertEquals("@article{Ph:0000,\n    title: {Test Article}\n    author: {PhpUnit}\n    pages: {10-15}\n}\n\n", $content);

        $this->refreshToken($response);
        $bib = Bibliography::first();
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
        $bib = Bibliography::first();
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->delete('/api/v1/bibliography/'.$bib->id);

        $response->assertStatus(204);
        $cnt = Bibliography::count();
        $this->assertEquals(0, $cnt);
        $this->assertEquals("", $response->getContent());
    }
}
