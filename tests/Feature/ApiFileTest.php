<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use Illuminate\Support\Facades\Storage;

use App\File;

class ApiFileTest extends TestCase
{
    private static $testFiles = [
        'text1.txt',
        'text2.txt',
        'text3.txt',
        'office_file.docx',
        'spacialist_screenshot.png',
        'test_img_edin.jpg',
        'test_archive.zip',
    ];

    private $fileTypes = [
        ['mime' => 'image/jpeg', 'type' => 'jpg', 'category' => 'image'],
        ['mime' => 'text/plain', 'type' => 'txt', 'category' => 'text'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $path = storage_path() . "/framework/testing/";
        foreach(self::$testFiles as $f) {
            $filehandle = fopen("$path$f", 'r');
            Storage::put(
                $f,
                $filehandle
            );
            fclose($filehandle);
        }
    }

    protected function createFiles($min=5, $max=10, $imagesOnly=false, $assert=false) {
        $amount = rand($min, $max);
        $ids = [];
        for($i = 0; $i<$amount; $i++) {
            if(!$imagesOnly) {
                $ft = array_rand($this->fileTypes);
                $type = $ft['type'];
                $mime = $ft['mime'];
                $cat = $ft['category'];
            } else {
                $type = 'jpg';
                $mime = 'image/jpeg';
                $cat = 'image';
            }
            $isImage = $type === 'jpg';
            $basename = $this->faker->bothify('test_file_###???###???');
            $filename = "$basename.$type";
            if($isImage) {
                $thumbname = $basename."_thumb.$type";
                $file = UploadedFile::fake()->image($filename);
                UploadedFile::fake()->image($thumbname);
            } else {
                $file = UploadedFile::fake()->create($filename);
                $thumbname = null;
            }

            $desc = $this->faker->text();

            $response = $this->withHeaders([
                    'Authorization' => "Bearer $this->token"
                ])
                ->post('/api/v1/file/new', [
                    'file' => $file,
                    'description' => $desc
                ]);

            $uplFile = File::latest()->first();
            $ids[] = $uplFile->id;

            if($assert) {
                Storage::assertExists($filename);
                if($isImage) {
                    Storage::assertExists($thumbname);
                }

                $response->assertStatus(201);
                $response->assertJsonStructure([
                    'id',
                    'name',
                    'modified',
                    'created',
                    'cameraname',
                    'exif',
                    'thumb',
                    'copyright',
                    'description',
                    'mime_type',
                    'lasteditor',
                    'created_at',
                    'updated_at',
                    'category'
                ]);

                $response->assertExactJson([
                    'id' => $uplFile->id,
                    'name' => $filename,
                    'modified' => $uplFile->modified,
                    'created' => $uplFile->created,
                    'cameraname' => null,
                    'exif' => null,
                    'thumb' => $thumbname,
                    'copyright' => null,
                    'description' => $desc,
                    'mime_type' => $mime,
                    'lasteditor' => 'Admin',
                    'created_at' => "$uplFile->created_at",
                    'updated_at' => "$uplFile->updated_at",
                    'category' => $cat
                ]);
            }

            $this->refreshToken($response);
        }

        return $ids;
    }

    // Testing GET requests

    /**
     * Test retrieving uploaded file (id=4).
     *
     * @return void
     */
    public function testGetPngFileEndpoint()
    {
        $file = File::find(4);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/4');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'modified',
            'modified_unix',
            'created',
            'created_unix',
            'size',
            'cameraname',
            'exif',
            'thumb',
            'copyright',
            'description',
            'mime_type',
            'lasteditor',
            'created_at',
            'updated_at',
            'url',
            'thumb_url',
            'category',
            'entities',
            'tags'
        ]);

        $name = 'spacialist_screenshot.png';
        $response->assertJson([
            'id' => 4,
            'name' => $name,
            'modified' => '2019-03-08 13:13:11',
            'modified_unix' => Storage::lastModified($name),
            'created' => '2019-03-08 13:13:11',
            'created_unix' => strtotime('2019-03-08 13:13:11'),
            'size' => Storage::size($name),
            'cameraname' => null,
            'exif' => null,
            'thumb' => 'spacialist_screenshot_thumb.jpg',
            'copyright' => null,
            'description' => null,
            'mime_type' => 'image/png',
            'lasteditor' => 'Admin',
            'created_at' => '2019-03-08 13:13:11',
            'updated_at' => '2019-03-08 13:13:11',
            'url' => "/storage/spacialist_screenshot.png",
            'thumb_url' => "/storage/spacialist_screenshot_thumb.jpg",
            'category' => 'image',
            'entities' => [
                ['id' => 3]
            ],
            'tags' => []
        ]);
    }

    /**
     * Test retrieving uploaded file (id=5).
     *
     * @return void
     */
    public function testGetJpgFileEndpoint()
    {
        $file = File::find(5);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'modified',
            'modified_unix',
            'created',
            'created_unix',
            'size',
            'cameraname',
            'exif',
            'thumb',
            'copyright',
            'description',
            'mime_type',
            'lasteditor',
            'created_at',
            'updated_at',
            'url',
            'thumb_url',
            'category',
            'entities',
            'tags'
        ]);

        $name = 'test_img_edin.jpg';
        $response->assertJson([
            'id' => 5,
            'name' => $name,
            'modified' => '2019-03-08 13:13:11',
            'modified_unix' => Storage::lastModified($name),
            'created' => '2017-06-18 19:46:37',
            'created_unix' => strtotime('2017-06-18 19:46:37'),
            'size' => Storage::size($name),
            'cameraname' => 'Canon EOS 650D (Canon)',
            'exif' => [

            ],
            'thumb' => 'test_img_edin_thumb.jpg',
            'copyright' => 'Vinzenz Rosenkranz (CC BY-NC-SA 2.0)',
            'description' => 'Edinburgh Castle',
            'mime_type' => 'image/jpeg',
            'lasteditor' => 'Admin',
            'created_at' => '2019-03-08 13:13:11',
            'updated_at' => '2019-03-08 13:13:12',
            'url' => "/storage/test_img_edin.jpg",
            'thumb_url' => "/storage/test_img_edin_thumb.jpg",
            'category' => 'image',
            'entities' => [
                ['id' => 1]
            ],
            'tags' => []
        ]);
    }

    /**
     * Test getting tree-like content of an archive file (id=6).
     *
     * @return void
     */
    public function testGetArchiveFileListEndpoint()
    {
        $file = File::find(5);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/6/archive/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'isDirectory',
                'path',
                'compressedSize',
                'uncompressedSize',
                'modificationTime',
                'isCompressed',
                'filename',
                'mtime',
                'cleanFilename'
            ]
        ]);

        $response->assertExactJson([
            [
                'isDirectory' => false,
                'path' => 'text2.txt',
                'compressedSize' => 38,
                'compressed_size' => 38,
                'uncompressedSize' => 36,
                'uncompressed_size' => 36,
                'modificationTime' => 1552044042,
                'isCompressed' => true,
                'is_compressed' => true,
                'filename' => 'text2.txt',
                'mtime' => 1552044042,
                'cleanFilename' => 'text2.txt'
            ],
            [
                'isDirectory' => false,
                'path' => 'text3.txt',
                'compressedSize' => 39,
                'compressed_size' => 39,
                'uncompressedSize' => 40,
                'uncompressed_size' => 40,
                'modificationTime' => 1552044062,
                'isCompressed' => true,
                'is_compressed' => true,
                'filename' => 'text3.txt',
                'mtime' => 1552044062,
                'cleanFilename' => 'text3.txt'
            ],
            [
                'isDirectory' => false,
                'path' => 'test_img_edin.jpg',
                'compressedSize' => 3391570,
                'compressed_size' => 3391570,
                'uncompressedSize' => 3407381,
                'uncompressed_size' => 3407381,
                'modificationTime' => 1498723284,
                'isCompressed' => true,
                'is_compressed' => true,
                'filename' => 'test_img_edin.jpg',
                'mtime' => 1498723284,
                'cleanFilename' => 'test_img_edin.jpg'
            ],
            [
                'children' => [
                    [
                        'isDirectory' => false,
                        'path' => 'folder/folder_text1.txt',
                        'compressedSize' => 45,
                        'compressed_size' => 45,
                        'uncompressedSize' => 46,
                        'uncompressed_size' => 46,
                        'modificationTime' => 1552049678,
                        'isCompressed' => true,
                        'is_compressed' => true,
                        'filename' => 'folder/folder_text1.txt',
                        'mtime' => 1552049678,
                        'cleanFilename' => 'folder_text1.txt'
                    ]
                ],
                'isDirectory' => true,
                'path' => 'folder/',
                'compressedSize' => 0,
                'uncompressedSize' => 0,
                'modificationTime' => 0,
                'isCompressed' => false,
                'filename' => 'folder/',
                'mtime' => 0,
                'cleanFilename' => 'folder'
            ],
        ]);
    }

    /**
     * Test getting the content of a word document as html (id=7).
     *
     * @return void
     */
    public function testGetDocAsHtmlEndpoint()
    {
        $file = File::find(5);
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/7/as_html');

        $response->assertStatus(200);
        $this->assertContains(
            'A test .docx file created in LibreOffice!',
            $response->getContent()
        );
    }

    /**
     * Test count of entities linked to a file (id=2) and non-linked file (id=1).
     *
     * @return void
     */
    public function testLinkCountEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/5/link_count');

        $response->assertStatus(200);
        $response->assertExactJson([1]);

        $this->refreshToken($response);

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/1/link_count');

        $response->assertStatus(200);
        $response->assertExactJson([0]);
    }

    /**
     * Test getting files of children of an entity (id=1) and (id=2).
     *
     * @return void
     */
    public function testSubFilesEndpoint()
    {
        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/1/sub_files');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertExactJson([
            [
                'id' => 3,
                'name' => 'text1.txt',
                'modified' => '2019-03-08 13:13:11',
                'modified_unix' => Storage::lastModified('text1.txt'),
                'created' => '2019-03-08 13:13:11',
                'created_unix' => 1552050791,
                'cameraname' => null,
                'thumb' => null,
                'copyright' => null,
                'description' => null,
                'mime_type' => 'text/plain',
                'lasteditor' => 'Admin',
                'created_at' => '2019-03-08 13:13:11',
                'updated_at' => '2019-03-08 13:13:11',
                'category' => 'text',
                'size' => Storage::size('text1.txt'),
                'exif' => null,
                'tags' => [],
                'url' => '/storage/text1.txt',
            ]
        ]);

        $this->refreshToken($response);

        $response = $this->withHeaders([
                'Authorization' => "Bearer $this->token"
            ])
            ->get('/api/v1/file/2/sub_files');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'modified',
                'modified_unix',
                'created',
                'created_unix',
                'cameraname',
                'thumb',
                'copyright',
                'description',
                'mime_type',
                'lasteditor',
                'created_at',
                'updated_at',
                'category',
                'size',
                'exif',
                'tags',
                'url',
            ]
        ]);
    }
}
