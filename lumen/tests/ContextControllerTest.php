<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class ContextControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEditorSearch()
    {
        $user = factory('App\User')->create();

        $this->assertEquals(
            $this->actingAs($user)->post('editor/search', [
                'val' => 'komm'
            ]),
            $this->actingAs($user)->post('editor/search', [
                'val' => 'komm',
                'lang' => 'de'
            ])
        );
    }
}
