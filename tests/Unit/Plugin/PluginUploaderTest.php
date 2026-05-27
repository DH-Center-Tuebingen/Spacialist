<?php

namespace Tests\Unit\Plugin;

use App\Plugin;
use App\Plugin\PluginDirectory;
use App\Plugin\PluginUploader;
use SplFileInfo;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\PluginDirectoryGenerator;
use Tests\Support\PluginTemplate;
use Tests\TestCase;
use ZipArchive;

class PluginUploaderTest extends TestCase {

    private const PLUGIN_NAME = 'UploadTestPlugin';
    private const PLUGIN_UUID = '00000000-0000-0000-0000-000000000099';

    private PluginUploader $uploader;
    private array $tempFiles = [];
    private array $dirsToCleanup = [];

    protected function setUp(): void {
        parent::setUp();
        $this->uploader = new PluginUploader();
    }

    protected function tearDown(): void {
        foreach($this->tempFiles as $path) {
            if(file_exists($path)) {
                unlink($path);
            }
        }
        foreach($this->dirsToCleanup as $dir) {
            PluginDirectoryGenerator::cleanup($dir);
        }
        parent::tearDown();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeTemplate(string $version = '1.0.0'): PluginTemplate {
        return new PluginTemplate(self::PLUGIN_NAME, self::PLUGIN_UUID, $version);
    }

    private function makeZipFromTemplate(PluginTemplate $template): string {
        $zipPath = $this->getTmpZipPath('test_plugin');
        $this->tempFiles[] = $zipPath;
        PluginDirectoryGenerator::mockPluginZipFile($template, $zipPath);
        return $zipPath;
    }

    private function trackPluginDirs(): void {
        $this->dirsToCleanup[] = PluginDirectory::getPath(self::PLUGIN_NAME);
        $this->dirsToCleanup[] = PluginDirectory::getPath('_backups/' . self::PLUGIN_NAME);
    }

    private function createPluginInDb(string $version): Plugin {
        $plugin = new Plugin();
        $plugin->name = self::PLUGIN_NAME;
        $plugin->uuid = self::PLUGIN_UUID;
        $plugin->version = $version;
        $plugin->save();
        return $plugin;
    }

    private function createPluginDir(): string {
        $dir = PluginDirectory::getPath(self::PLUGIN_NAME);
        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
    
    private function getTmpZipPath(string $filename): string {
        return sys_get_temp_dir() . '/' . uniqid($filename . "_") . '.zip';
    }

    // -----------------------------------------------------------------------
    // upload() — happy paths
    // -----------------------------------------------------------------------

    public function testUploadCreatesNewPlugin(): void {
        $this->trackPluginDirs();
        $template = $this->makeTemplate('1.0.0')->addBasic()->generate();
        $zipPath = $this->makeZipFromTemplate($template);

        $result = $this->uploader->upload(new SplFileInfo($zipPath));

        $this->assertEquals(self::PLUGIN_NAME, $result->pluginName);
        $this->assertFalse($result->isUpdate());
        $this->assertTrue($this->uploader->doesPluginDirectoryExist(self::PLUGIN_NAME));
    }

    public function testUploadUpdatesExistingPlugin(): void {
        $this->trackPluginDirs();
        $this->createPluginInDb('1.0.0');
        $this->createPluginDir();

        $template = $this->makeTemplate('1.0.1')->addBasic()->generate();
        $zipPath = $this->makeZipFromTemplate($template);

        $result = $this->uploader->upload(new SplFileInfo($zipPath));

        $this->assertEquals(self::PLUGIN_NAME, $result->pluginName);
        $this->assertTrue($result->isUpdate());
        $this->assertTrue($this->uploader->doesPluginDirectoryExist(self::PLUGIN_NAME));
        $this->assertTrue(is_dir(PluginDirectory::getPath('_backups/' . self::PLUGIN_NAME)));
    }

    public function testUploadMovesOldPluginToBackupOnUpdate(): void {
        $this->trackPluginDirs();
        $this->createPluginInDb('1.0.0');
        $existingDir = $this->createPluginDir();
        // Plant a sentinel file in the existing plugin dir so we can confirm it was backed up
        file_put_contents($existingDir . '/sentinel.txt', 'old version marker');

        $template = $this->makeTemplate('1.0.1')->addBasic()->generate();
        $zipPath = $this->makeZipFromTemplate($template);

        $this->uploader->upload(new SplFileInfo($zipPath));

        $backupSentinel = PluginDirectory::getPath('_backups/' . self::PLUGIN_NAME) . '/sentinel.txt';
        $this->assertTrue(file_exists($backupSentinel));
    }

    // -----------------------------------------------------------------------
    // upload() — error paths
    // -----------------------------------------------------------------------

    public function testUploadRejectsInvalidZipFile(): void {
        $fakePath = $this->getTmpZipPath('fake');
        file_put_contents($fakePath, 'this is not a zip file');
        $this->tempFiles[] = $fakePath;

        $this->expectException(HttpException::class);
        $this->uploader->upload(new SplFileInfo($fakePath));
    }

    public function testUploadRejectsZipWithMultipleRoots(): void {
        $zipPath = $this->getTmpZipPath('multi_root');
        $this->tempFiles[] = $zipPath;

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('PluginA/plugin.xml', '<plugin><name>PluginA</name><version>1.0.0</version></plugin>');
        $zip->addFromString('PluginB/plugin.xml', '<plugin><name>PluginB</name><version>1.0.0</version></plugin>');
        $zip->close();

        $this->expectException(HttpException::class);
        $this->uploader->upload(new SplFileInfo($zipPath));
    }

    public function testUploadRejectsSameVersion(): void {
        $this->trackPluginDirs();
        $this->createPluginInDb('1.0.0');

        $template = $this->makeTemplate('1.0.0')->addBasic()->generate();
        $zipPath = $this->makeZipFromTemplate($template);

        $this->expectException(HttpException::class);
        $this->uploader->upload(new SplFileInfo($zipPath));
    }

    public function testUploadRejectsOlderVersion(): void {
        $this->trackPluginDirs();
        $this->createPluginInDb('2.0.0');

        $template = $this->makeTemplate('1.0.0')->addBasic()->generate();
        $zipPath = $this->makeZipFromTemplate($template);

        $this->expectException(HttpException::class);
        $this->uploader->upload(new SplFileInfo($zipPath));
    }

    // -----------------------------------------------------------------------
    // restoreBackup()
    // -----------------------------------------------------------------------

    public function testRestoreBackupReturnsFalseWhenNoBackupExists(): void {
        $result = $this->uploader->restoreBackup(self::PLUGIN_NAME);
        $this->assertFalse($result);
    }

    public function testRestoreBackupSucceeds(): void {
        $backupDir = PluginDirectory::getPath('_backups/' . self::PLUGIN_NAME);
        $this->dirsToCleanup[] = PluginDirectory::getPath(self::PLUGIN_NAME);
        $this->dirsToCleanup[] = $backupDir;

        mkdir($backupDir, 0755, true);
        file_put_contents($backupDir . '/test.txt', 'backup content');

        $result = $this->uploader->restoreBackup(self::PLUGIN_NAME);

        $this->assertTrue($result);
        $this->assertTrue($this->uploader->doesPluginDirectoryExist(self::PLUGIN_NAME));
        $this->assertFalse(file_exists($backupDir));
    }

    public function testRestoreBackupReplacesExistingPlugin(): void {
        $pluginDir = PluginDirectory::getPath(self::PLUGIN_NAME);
        $backupDir = PluginDirectory::getPath('_backups/' . self::PLUGIN_NAME);
        $this->dirsToCleanup[] = $pluginDir;
        $this->dirsToCleanup[] = $backupDir;

        mkdir($pluginDir, 0755, true);
        file_put_contents($pluginDir . '/old.txt', 'old content');
        mkdir($backupDir, 0755, true);
        file_put_contents($backupDir . '/backup.txt', 'backup content');

        $result = $this->uploader->restoreBackup(self::PLUGIN_NAME);

        $this->assertTrue($result);
        $this->assertFalse(file_exists($pluginDir . '/old.txt'), 'Old plugin file should be removed');
        $this->assertTrue(file_exists($pluginDir . '/backup.txt'), 'Backup file should be in plugin dir');
    }

    // -----------------------------------------------------------------------
    // doesPluginDirectoryExist()
    // -----------------------------------------------------------------------

    public function testDoesPluginDirectoryExistReturnsTrueWhenDirectoryExists(): void {
        $dir = $this->createPluginDir();
        $this->dirsToCleanup[] = $dir;

        $this->assertTrue($this->uploader->doesPluginDirectoryExist(self::PLUGIN_NAME));
    }

    public function testDoesPluginDirectoryExistReturnsFalseWhenDirectoryIsMissing(): void {
        $this->assertFalse($this->uploader->doesPluginDirectoryExist('NonExistentPlugin_' . uniqid()));
    }
}
