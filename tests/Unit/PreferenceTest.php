<?php

namespace Tests\Unit;

use Tests\TestCase;


use App\Preference;

class PreferenceTest extends TestCase
{
    /**
     * Test decodePreference() method
     *
     * @return void
     */
    public function testDecodePreference()
    {
        $preferences = [
            [
                'label' => 'prefs.not-existing',
                'value' => 'input',
                'testValue' => 'input'
            ],
        ];

        foreach($preferences as $p) {
            $value = Preference::decodePreference($p['label'], $p['value']);
            $this->assertEquals($p['testValue'], $value);
        }
    }

    /**
     * Test encodePreference() method
     *
     * @return void
     */
    public function testEncodePreference()
    {
        $preferences = [
            [
                'label' => 'prefs.gui-language',
                'value' => 'de',
                'testValue' => '{"language_key":"de"}'
            ],
            [
                'label' => 'prefs.columns',
                'value' => '{"left":2, "right":5, "center":5}',
                'testValue' => '{"left":2, "right":5, "center":5}'
            ],
            [
                'label' => 'prefs.show-tooltips',
                'value' => false,
                'testValue' => '{"show":false}'
            ],
            [
                'label' => 'prefs.tag-root',
                'value' => 'http://example.com',
                'testValue' => '{"uri":"http:\/\/example.com"}'
            ],
            [
                'label' => 'prefs.load-extensions',
                'value' => '{"map":true, "files":false}',
                'testValue' => '{"map":true, "files":false}'
            ],
            [
                'label' => 'prefs.link-to-thesaurex',
                'value' => 'http://example.com',
                'testValue' => '{"url":"http:\/\/example.com"}'
            ],
            [
                'label' => 'prefs.project-name',
                'value' => 'Unit Testing',
                'testValue' => '{"name":"Unit Testing"}'
            ],
            [
                'label' => 'prefs.project-maintainer',
                'value' => '{"name":"PHPUnit", "email":"admin@admin.com", "public":true, "description": "None"}',
                'testValue' => '{"name":"PHPUnit", "email":"admin@admin.com", "public":true, "description": "None"}'
            ],
            [
                'label' => 'prefs.map-projection',
                'value' => '{"epsg": "4326"}',
                'testValue' => '{"epsg": "4326"}'
            ],
            [
                'label' => 'prefs.enable-password-reset-link',
                'value' => false,
                'testValue' => '{"use":false}'
            ],
        ];

        foreach($preferences as $p) {
            $value = Preference::encodePreference($p['label'], $p['value']);
            $this->assertEquals($p['testValue'], $value);
        }
    }
}
