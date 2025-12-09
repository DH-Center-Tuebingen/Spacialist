<?php

use App\Entity;
use Illuminate\Http\UploadedFile;


/**
 *
 * Utilities
 *
 */
function createDefaultCSVFile($delimiter = ",", $hasHeaderRow = true)
{
    $columns = [
        ['Yamato', 'Site A\\\\Befund 1\\\\Inv. 1234', 'yellow', 'lee;steve;dave'],
        ['大和', '', '黃色的', '王;伟祺;平安'],
        ['やまと', 'Site A', 'きいろい', "ひまり;まゆみ;ユイ"],
        ['ياماتو', 'Site B', 'أصفر', "عَلِي;يُوْسِف;حَسَن"],
        ['Site A', '', 'Updated', 'Alternative Site']
    ];

    if($hasHeaderRow) {
        array_unshift($columns, ['name', 'parent', 'Notizen', 'Alternativer Name']);
    }

    return createCSVFile($columns, $delimiter);
}

function createDimensionsCSVFile($delimiter = ",", $hasHeaderRow = true)
{
    $columns = [
        ['imported', 'Site A', '1;2;3;cm'],
    ];

    if($hasHeaderRow) {
        array_unshift($columns, ['name', 'parent', 'Abmessungen']);
    }

    return createCSVFile($columns, $delimiter);
}

function createCSVFile(array $tableData, $delimiter = ",")
{
    $content = '';
    if(!empty($tableData)) {
        foreach($tableData as $row) {
            // Cells need to be escaped, as some cells may contain elements that collide with the delimiter
            // e.g. lists are separated by semicolons
            $escapeAllRows = array_map(fn ($cell) => "\"$cell\"", $row);
            $content .= implode($delimiter, $escapeAllRows) . "\n";
        }
    }

    return UploadedFile::fake()->createWithContent('data.csv', $content);
}

function getData(?string $name = 'name', int $entityTypeId = 3, array $attributes = [], ?string $parentColumn = null)
{
    return json_encode([
        "name_column" => $name,
        "parent_column" => $parentColumn,
        "entity_type_id" => $entityTypeId,
        "attributes" => $attributes,
    ]);
}

function getDimensionsData()
{
    return getData(parentColumn: 'parent', entityTypeId: 6, attributes: [
        9 => "Abmessungen",
    ]);
}

function getMetaData(string $delimiter = ",", bool $hasHeaderRow = true)
{
    return json_encode([
        'delimiter' => $delimiter,
        'has_header_row' => $hasHeaderRow,
        'encoding' => 'UTF-8'
    ]);
}

test('validation empty file', function () {
    $file = createCSVFile([]);
    $metadata = getMetaData();

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(),
        'metadata' => getMetaData(hasHeaderRow: false),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [
                "No rows to import"
            ],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation empty with headers', function () {
    $file = createCSVFile([['name', 'parent', 'type']]);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [
                "No rows to import"
            ],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation names column missing error', function () {
    $file = createCSVFile([['other'], [''], ['other 2']]);

    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: ''),
        'metadata' => getMetaData(),
    ])->assertStatus(200);

    $response->assertJson([
        "errors" => [
            "Required column is missing: name_column"
        ],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation names column null error', function () {
    $file = createCSVFile([['other'], [''], ['other 2']]);

    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: null),
        'metadata' => getMetaData(),
    ])->assertStatus(200);

    $response->assertJson([
        "errors" => [
            "Required column is missing: name_column"
        ],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation with all names set correctly', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with invalid type id', function () {
    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(entityTypeId: -1),
        'metadata' => getMetaData(),
    ])->assertStatus(200);

    $response->assertJson([
        'errors' => ['The entity type does not exist: -1'],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation invalid file', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => null,
        'data' => getData(),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(422)
        ->assertJson([
            "message" => "The file field is required.",
            "errors" => [
                "file" => [
                    "The file field is required."
                ]
            ]
        ]);
});

test('validation incorrect delimiter', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(';'),
        'data' => getData(),
        'metadata' => getMetaData(delimiter: ','),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => ["The column for the name does not exist: name"],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation correct delimiter', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(';'),
        'data' => getData(),
        'metadata' => getMetaData(delimiter: ';'),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(parentColumn: 'parent'),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column child type not allowed', function () {
    $file = createCSVFile([
        ['name', 'parent', 'Notizen'],
        ['parent not allowed', 'Site A\\\\Befund 1\\\\Inv. 31', '6 is parent of 3']
    ]);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(parentColumn: 'parent'),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['[2] The relationship between entity types is not allowed: Stone -> Site'],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation error on non top entity as top', function () {
    $file = createCSVFile([
        ['name', 'parent', 'Notizen'],
        ['Feature #1', '', 'Feature is not a top level entity.']
    ]);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(parentColumn: 'parent', entityTypeId: 4), 
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['[2] The relationship between entity types is not allowed: TOP -> Feature'],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation with parent column top level element already exists', function () {
    $file = createCSVFile([
        ['name', 'parent', 'Notizen'],
        ['Site A', '', '']
    ]);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(parentColumn: 'parent'),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 0,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column sub element already exists', function () {
    $file = createCSVFile([
        ['name', 'parent', 'Notizen'],
        ['Fund 12', 'Site B', 'Fund 12 does already exist at this location.']
    ]);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(parentColumn: 'parent'),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 0,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation of attributes', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            8 => "Notizen",
            15 => "Alternativer Name"
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation of attributes invalid column name', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            8 => "nots",
            15 => "alts"
        ]),
        'metadata' => getMetaData(),
    ])->assertJson([
        'errors' => ["The attribute columns do not exist: nots, alts"],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation of attributes invalid attribute id', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            -1 => "Notizen",
            -1 => "Alternativer Name"
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['The attribute id does not exist: -1'],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation of attributes invalid attribute id and invalid column name', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            -1 => "nots",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [
                'The attribute id does not exist: -1',
                'The attribute columns do not exist: nots',
            ],
            'summary' => [
                'create' => 0,
                'update' => 0,
                'conflict' => 2
            ]
        ]);
});

test('validation of attribute value succeeds', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createCSVFile([
            ['name', 'parent', 'Abmessungen'],
            ['good', 'Site A', '1;2;3;cm'],
        ]),
        'data' => getData(parentColumn: 'parent', entityTypeId: 6, attributes: [
            9 => "Abmessungen",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            'summary' => [
                'create' => 1,
                'update' => 0,
                'conflict' => 0
            ]
        ]);
});

test('validation of attribute value fails', function () {
    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createCSVFile([
            ['name', 'parent', 'Abmessungen'],
            ['bad',  'Site A', '1,2,3,cm'],
        ]),
        'data' => getData(parentColumn: 'parent', entityTypeId: 6, attributes: [
            9 => "Abmessungen",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['[2] Attribute could not be imported: {{Abmessungen}} => {{1,2,3,cm}}'],
            'summary' => [
                'create' => 1,
                'update' => 0,
                'conflict' => 1,
            ]
        ]);
});

test('validation fails with mismatching columns', function () {
    $headerRow = ["name", "parent", "Notes", "Description"];
    $headerRowString = implode(',', $headerRow);
    $headerCount = count($headerRow);
    $dataRow = ["Site A", "", "updated"];
    $dataRowString = implode(',', $dataRow);
    $dataCount = count($dataRow);
    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createCSVFile([
            $headerRow,
            $dataRow,
        ]),
        'data' => getData(),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(200);

    $response->assertJson([
        'errors' => [
            "Column count of current row '{$dataRowString}' ({$dataCount} columns) does not match column count in header row '{$headerRowString}' ({$headerCount} columns)."
        ],
    ]);
});

test('validation with duplicate column mapping', function () {
    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            13 => "Notizen", // Notizen
            19 => "Notizen", // Aufbewahrung
        ]),
        'metadata' => getMetaData(),
    ]);
    $response->assertStatus(200);

    $response->assertJson([
        'errors' => [],
        'summary' => [
            'create' => 4,
            'update' => 1,
            'conflict' => 0
        ]
    ]);
});

test('data import success', function () {
    $response = $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createDimensionsCSVFile(),
        'data' => getDimensionsData(),
        'metadata' => getMetaData(),
    ]);

    if($response->status() !== 201) {
        $response->assertJson([
            'error' => 'any'
        ]);
    }
    $response->assertStatus(201);

    $entityId = null;
    try {
        $entityId = Entity::getFromPath('Site A\\\\imported');
    } catch(\Exception $e) {
        $this->fail('Entity not found');
    }

    expect($entityId)->not->toBeNull();
    $entity = Entity::find($entityId);
    expect($entity)->not->toBeNull();

    expect($entity->name)->toEqual('imported');
    $entityData = $entity->getData();
    expect($entityData[9]->value)->toEqual(json_decode('{"B":1,"H":2,"T":3,"unit":"cm"}'));
});

test('data import fails on empty file', function () {
    $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createCSVFile([]),
        'data' => getData(),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(400)
        ->assertJson([
            'error' => 'No rows to import'
        ]);
});

test('data import can create attribute', function () {
    $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createCSVFile([["name", "parent", "Notizen"], ["Site A", "", "updated"]]),
        'data' => getData(attributes: [
            8 => "Notizen",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(201);

    $entityId = Entity::getFromPath('Site A');
    $data = Entity::find($entityId)->getData();
    expect($data[8]->value)->toEqual('updated');
});

test('data import can update attribute', function () {
    $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createCSVFile([["name", "parent", "Alternativer Name"], ["Site A", "", "alt name;alias"]]),
        'data' => getData(attributes: [
            15 => "Alternativer Name",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(201);

    $entityId = Entity::getFromPath('Site A');
    $data = Entity::find($entityId)->getData();
    expect($data[15]->value)->toEqual(["alt name", "alias"]);
});

test('data import fails with incompatible mapping', function () {
    $response = $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createCSVFile([["name", "parent", "Notizen"], ["Site A", "", "updated"]]),
        'data' => getData(attributes: [
            99 => "Notizen",
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(400);

    $response->assertJson([
        'error' => 'The attribute id does not exist: 99',
        'data' => [
            'count' => -1,
            'entry' => null,
            'on' => -1,
            'on_index' => -1,
            'on_value' => null,
            'on_name' => null
        ]
    ]);
});

test('validation empty file no headers', function () {
    $file = createCSVFile([]);
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [
                "No rows to import"
            ],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation names column missing error no headers', function () {
    $file = createCSVFile([['other'], [''], ['other 2']]);
    $metadata = getMetaData(hasHeaderRow: false);

    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: '#4'),
        'metadata' => $metadata,
    ])->assertStatus(200);

    $response->assertJson([
        "errors" => [
            "The column for the name does not exist: #4"
        ],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation names column null error no headers', function () {
    $file = createCSVFile([['other'], [''], ['other 2']]);
    $metadata = getMetaData(hasHeaderRow: false);

    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: null),
        'metadata' => $metadata,
    ])->assertStatus(200);

    $response->assertJson([
        "errors" => [
            "Required column is missing: name_column"
        ],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation with all names set correctly no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: '#1'),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with invalid type id no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $response = $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", entityTypeId: -1),
        'metadata' => $metadata,
    ])->assertStatus(200);

    $response->assertJson([
        'errors' => ['The entity type does not exist: -1'],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation invalid file no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => null,
        'data' => getData(name: "#1"),
        'metadata' => $metadata,
    ])
        ->assertStatus(422)
        ->assertJson([
            "message" => "The file field is required.",
            "errors" => [
                "file" => [
                    "The file field is required."
                ]
            ]
        ]);
});

test('validation incorrect delimiter no headers', function () {
    $metadata = getMetaData(delimiter: ',', hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false, delimiter:';'),
        'data' => getData(),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => ["The column for the name does not exist: name"],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation correct delimiter no headers', function () {
    $metadata = getMetaData(delimiter: ';', hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false, delimiter:';'),
        'data' => getData(name: "#1"),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", parentColumn: '#2'),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            "errors" => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column child type not allowed no headers', function () {
    $file = createCSVFile([
        ['parent not allowed', 'Site A\\\\Befund 1\\\\Inv. 31', '6 is parent of 3']
    ]);
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: "#1", parentColumn: '#2'),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['[1] The relationship between entity types is not allowed: Stone -> Site'],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation with parent column top level element already exists no headers', function () {
    $file = createCSVFile([
        ['Site A', '', '']
    ]);
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: "#1", parentColumn: '#2'),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 0,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation with parent column sub element already exists no headers', function () {
    $file = createCSVFile([
        ['Fund 12', 'Site B', 'Fund 12 does already exist at this location.']
    ]);
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => $file,
        'data' => getData(name: "#1", parentColumn: '#2'),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 0,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation of attributes no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", attributes: [
        8 => "#3",
        15 => "#4"
    ]),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            "summary" => [
                "create" => 4,
                "update" => 1,
                "conflict" => 0
            ]
        ]);
});

test('validation of attributes invalid column name no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", attributes: [
        8 => "#10",
        15 => "#11"
    ]),
        'metadata' => $metadata,
    ])->assertJson([
        'errors' => ["The attribute columns do not exist: #10, #11"],
        "summary" => [
            "create" => 0,
            "update" => 0,
            "conflict" => 1
        ]
    ]);
});

test('validation of attributes invalid attribute id no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", attributes: [
        -1 => "#3",
        -1 => "#4"
    ]),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['The attribute id does not exist: -1'],
            "summary" => [
                "create" => 0,
                "update" => 0,
                "conflict" => 1
            ]
        ]);
});

test('validation of attributes invalid attribute id and invalid column name no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createDefaultCSVFile(hasHeaderRow: false),
        'data' => getData(name: "#1", attributes: [
        -1 => "#11",
    ]),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [
                'The attribute id does not exist: -1',
                'The attribute columns do not exist: #11',
            ],
            'summary' => [
                'create' => 0,
                'update' => 0,
                'conflict' => 2
            ]
        ]);
});

test('validation of attribute value succeeds no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createCSVFile([
            ['good', 'Site A', '1;2;3;cm'],
        ]),
        'data' => getData(name: "#1", parentColumn: '#2', entityTypeId: 6, attributes: [
            9 => "#3",
        ]),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => [],
            'summary' => [
                'create' => 1,
                'update' => 0,
                'conflict' => 0
            ]
        ]);
});

test('validation of attribute value fails no headers', function () {
    $metadata = getMetaData(hasHeaderRow: false);

    $this->userRequest()->post('/api/v1/entity/import/validate', [
        'file' => createCSVFile([
            ['bad',  'Site A', '1,2,3,cm'],
        ]),
        'data' => getData(name: "#1", parentColumn: '#2', entityTypeId: 6, attributes: [
            9 => "#3",
        ]),
        'metadata' => $metadata,
    ])
        ->assertStatus(200)
        ->assertJson([
            'errors' => ['[1] Attribute could not be imported: {{#3}} => {{1,2,3,cm}}'],
            'summary' => [
                'create' => 1,
                'update' => 0,
                'conflict' => 1,
            ]
        ]);
});

test('data import fails with duplicate column mapping', function () {
    $response = $this->userRequest()->post('/api/v1/entity/import', [
        'file' => createDefaultCSVFile(),
        'data' => getData(attributes: [
            13 => "Notizen", // Notizen
            19 => "Notizen", // Aufbewahrung
        ]),
        'metadata' => getMetaData(),
    ])
        ->assertStatus(201);
});
