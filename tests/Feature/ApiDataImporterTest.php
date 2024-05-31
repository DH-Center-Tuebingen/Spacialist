<?php

namespace Tests\Feature;

use App\Entity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

use function PHPUnit\Framework\assertJson;

class ApiDataImporterTest extends TestCase {

    use DatabaseTransactions;


    /**
     * 
     * Utilities
     * 
     */
    private function createDefaultCSVFile($delimiter = ",") {
        return $this->createCSVFile([
            ['name', 'parent', 'Notizen', 'Alternativer Name'],
            ['Yamato', 'Site A\\\\Befund 1\\\\Inv. 1234', 'yellow', 'lee;steve;dave'],
            ['大和', '', '黃色的', '王;伟祺;平安'],
            ['やまと', 'Site A', 'きいろい', "ひまり;まゆみ;ユイ"],
            ['ياماتو', 'Site B', 'أصفر', "عَلِي;يُوْسِف;حَسَن"],
        ], $delimiter);
    }

    private function createDimensionsCSVFile($delimiter = ",") {
        return $this->createCSVFile([
            ['name', 'parent', 'Abmessungen'],
            ['imported', 'Site A', '1;2;3;cm'],
        ], $delimiter);
    }

    private function createCSVFile(array $tableData, $delimiter = ",") {
        $content = '';
        if (!empty($tableData)) {
            foreach ($tableData as $row) {
                // Cells need to be escaped, as some cells may contain elements that collide with the delimiter
                // e.g. lists are separated by semicolons
                $escapeAllRows = array_map(fn ($cell) => "\"$cell\"", $row);
                $content .= implode($delimiter, $escapeAllRows) . "\n";
            }
        }

        return UploadedFile::fake()->createWithContent('data.csv', $content);
    }

    private function getData(?string $name = 'name', int $entityTypeId = 3, array $attributes = [], ?string $parentColumn = null) {
        return json_encode([
            "name_column" => $name,
            "parent_column" => $parentColumn,
            "entity_type_id" => $entityTypeId,
            "attributes" => $attributes,
        ]);
    }

    private function getDimensionsData(){
        return $this->getData(
            parentColumn: 'parent',
            entityTypeId: 6,
            attributes: [
                9 => "Abmessungen",
            ]
            );
    }

    private function getMetaData(string $delimiter = ",", bool $hasHeaderRow = true) {
        return json_encode([
            'delimiter' => $delimiter,
            'has_header_row' => $hasHeaderRow,
            'encoding' => 'UTF-8'
        ]);
    }

    /**
     * 
     * Tests
     * 
     */

    public function testValidationEmptyFile() {
        $file = $this->createCSVFile([]);
        $metadata = $this->getMetaData();

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(hasHeaderRow: false),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => ['entity-importer.empty']
        ]);
    }

    public function testValidationEmptyWithHeaders() {
        $file = $this->createCSVFile([['name']]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.empty']
        ]);
    }

    public function testValidationNamesColumnMissingError() {
        $file = $this->createCSVFile([['other'], [''], ['other 2']]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(name: ''),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.missing-data']
        ]);
    }

    public function testValidationNamesColumnNullError() {
        $file = $this->createCSVFile([['other'], [''], ['other 2']]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(name: ''),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.missing-data']
        ]);
    }

    public function testValidationWithAllNamesSetCorrectly() {
        $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(200);
    }

    public function testValidationWithInvalidTypeId() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(entityTypeId: -1),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.entity-type-does-not-exist']
        ]);
    }

    public function testValidationInvalidFile() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => null,
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(422);

        $response->assertJson([
            "message" => "The file field is required.",
            "errors" => [
                "file" => [
                    "The file field is required."
                ]
            ]
        ]);
    }

    public function testValidationIncorrectDelimiter() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(';'),
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(delimiter: ','),
        ])->assertStatus(400);
    }

    public function testValidationCorrectDelimiter() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(';'),
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(delimiter: ';'),
        ])->assertStatus(200);
    }

    public function testValidationWithParentColumn() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(parentColumn: 'parent'),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(200);
    }

    public function testValidationWithParentColumnChildTypeNotAllowed() {
        $file = $this->createCSVFile([
            ['name', 'parent', 'Notizen'],
            ['parent not allowed', 'Site A\\\\Befund 1\\\\Inv. 31', '3 is not allowed as child of 7']
        ]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(parentColumn: 'parent'),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['2: entity-importer.entity-type-relation-not-allowed']
        ]);
    }

    public function testValidationWithParentColumnTopLevelElementAlreadyExists() {
        $file = $this->createCSVFile([
            ['name', 'parent', 'Notizen'],
            ['Site A', '', '']
        ]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(parentColumn: 'parent'),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['2: entity-importer.entity-already-exists']
        ]);
    }

    public function testValidationWithParentColumnSubElementAlreadyExists() {
        $file = $this->createCSVFile([
            ['name', 'parent', 'Notizen'],
            ['Fund 12', 'Site B', 'Fund 12 does already exist at this location.']
        ]);

        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $file,
            'data' => $this->getData(parentColumn: 'parent'),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['2: entity-importer.entity-already-exists']
        ]);
    }

    public function testValidationOfAttributes() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(attributes: [
                8 => "Notizen",
                15 => "Alternativer Name"
            ]),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(200);
    }

    public function testValidationOfAttributesInvalidColumnName() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(attributes: [
                8 => "nots",
            ]),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.attribute-column-does-not-exist']
        ]);
    }

    public function testValidationOfAttributesInvalidAttributeId() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(attributes: [
                -1 => "Notizen",
            ]),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => ['entity-importer.attribute-does-not-exist']
        ]);
    }

    public function testValidationOfAttributesInvalidAttributeIdAndInvalidColumnName() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createDefaultCSVFile(),
            'data' => $this->getData(attributes: [
                -1 => "nots",
            ]),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400);

        $response->assertJson([
            'error' => [
                'entity-importer.attribute-does-not-exist',
                'entity-importer.attribute-column-does-not-exist',
            ]
        ]);
    }

    public function testValidationOfAttributeValueSucceeds() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createCSVFile([
                ['name', 'parent', 'Abmessungen'],
                ['good', 'Site A', '1;2;3;cm'],
            ]),
            'data' => $this->getData(
                parentColumn: 'parent',
                entityTypeId: 6,
                attributes: [
                    9 => "Abmessungen",
                ]
            ),
            'metadata' => $this->getMetaData(),
        ]);

        $response->assertStatus(200);
    }

    public function testValidationOfAttributeValueFails() {
        $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
            'file' => $this->createCSVFile([
                ['name', 'parent', 'Abmessungen'],
                ['bad',  'Site A', '1,2,3,cm'],
            ]),
            'data' => $this->getData(
                parentColumn: 'parent',
                entityTypeId: 6,
                attributes: [
                    9 => "Abmessungen",
                ]
            ),
            'metadata' => $this->getMetaData(),
        ])->assertStatus(400)->assertJson([
            'error' => ['2: entity-importer.attribute-could-not-be-imported']
        ]);
    }

    public function testDataImportSuccess() {
        $response = $this->userRequest()->post('/api/v1/entity/import', [
            'file' => $this->createDimensionsCSVFile(),
            'data' => $this->getDimensionsData(),
            'metadata' => $this->getMetaData(),
        ]);

        if($response->status() !== 201){
            $response->assertJson([
                'error' => 'any'
            ]);
        }

        $response->assertStatus(201);



        $entityId = null;
        try {
            $entityId = Entity::getFromPath('Site A\\\\imported');
        } catch (\Exception $e) {
            $this->fail('Entity not found');
        }

        $this->assertNotNull($entityId);
        $entity = Entity::find($entityId);
        $this->assertNotNull($entity);

        $this->assertEquals('imported', $entity->name);
        $entityData = $entity->getData();
        $this->assertEquals(json_decode('{"B":1,"H":2,"T":3,"unit":"cm"}'), $entityData[9]->value);
    }

    public function testDataImportFailsOnEmptyFile(){
        $this->userRequest()->post('/api/v1/entity/import', [
            'file' => $this->createCSVFile([]),
            'data' => $this->getData(),
            'metadata' => $this->getMetaData(),
        ])
        ->assertStatus(400)
        ->assertJson([
            'error' => 'entity-importer.empty'
        ]);
    }

    public function testDataImportCanCreateAttribute(){
        $this->userRequest()->post('/api/v1/entity/import', [
            'file' => $this->createCSVFile([["name", "parent", "Notizen"],["Site A", "", "updated"]]),
            'data' => $this->getData(
                attributes: [
                    8 => "Notizen",
                ]
            ),
            'metadata' => $this->getMetaData(),
        ])
        ->assertStatus(201);
        
        $entityId = Entity::getFromPath('Site A');
        $data = Entity::find($entityId)->getData();
        $this->assertEquals('updated', $data[8]->value);
    }

    public function testDataImportCanUpdateAttribute(){
        $this->userRequest()->post('/api/v1/entity/import', [
            'file' => $this->createCSVFile([["name", "parent", "Alternativer Name"],["Site A", "", "alt name;alias"]]),
            'data' => $this->getData(
                attributes: [
                    15 => "Alternativer Name",
                ]
            ),
            'metadata' => $this->getMetaData(),
        ])
        ->assertStatus(201);
        
        $entityId = Entity::getFromPath('Site A');
        $data = Entity::find($entityId)->getData();
        $this->assertEquals(["alt name", "alias"], $data[15]->value);
    }

    public function testDataImportFailsWithIncompatibleMapping(){
        $response = $this->userRequest()->post('/api/v1/entity/import', [
            'file' => $this->createCSVFile([["name", "parent", "Notizen"],["Site A", "", "updated"]]),
            'data' => $this->getData(
                attributes: [
                   99 => "Notizen",
                ]
            ),
            'metadata' => $this->getMetaData(),
        ])
        ->assertStatus(400);
        
        $response->assertJson([
            'error' => 'entity-importer.attribute-does-not-exist',
            'data' => [
                'count' => -1,
                'entry' => null,
                'on' => -1,
                'on_index' => -1,
                'on_value' => null,
                'on_name' => null
            ] 
        ]);
    }


}
