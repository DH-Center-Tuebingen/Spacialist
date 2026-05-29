<?php

namespace Tests\Unit\Support\Log;

use Carbon\Carbon;
use Tests\TestCase;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Monolog\Logger as MonologLogger;

use PHPUnit\Framework\Attributes\DataProvider;

use App\Support\Log\PluginLog;

class PluginLogTest extends TestCase {

    const LOG_MESSAGE = "This is a test log message.";

    public function setUp(): void {
        parent::setUp();
        // Clear the plugin log file before each test
        $this->clearLogFile();
    }

    private function clearLogFile() {
        $logFilePath = storage_path('logs/plugin.log');
        if(file_exists($logFilePath)) {
            file_put_contents($logFilePath, '');
        }
    }

    private function getExpectedMessage(string $levelString){
        return 'testing.'. strtoupper($levelString) . ': ' . self::LOG_MESSAGE;
    }
    
    private function getExpectedPluginMessage(string $pluginName,string $levelString){
        return 'testing.'. strtoupper($levelString) . ': [' . strtoupper($pluginName) . '] ' . self::LOG_MESSAGE;
    }

    /**
     * Returns the content of plugin.log with the timestamps removed.
     * @return string
     */
    private function getLogContent(): string {
        $content = trim(File::get(storage_path('logs/plugin.log')));
        // As we cannot simply set the timestamp to a fixed value using Carbon::setTestNow, we just ignore it.
        return  preg_replace('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]\s/', '', $content);
    }

    #[DataProvider('logLevelDataProvider')]
    public function testLogFunctions($level, $method) {
        $logger = (new PluginLog('Test'));
        $logger->$method(self::LOG_MESSAGE);
        $logContent = $this->getLogContent();
        $this->assertEquals($this->getExpectedPluginMessage('Test', $level), $logContent);
    }

    #[DataProvider('logLevelDataProvider')]
    public function testStaticLogFunctions($level, $method) {
        $staticMethod = 'log' . ucfirst($method);
        PluginLog::$staticMethod(self::LOG_MESSAGE);
        $logContent = $this->getLogContent();
        $this->assertEquals($this->getExpectedMessage($level), $logContent);
    }

    public static function logLevelDataProvider() {
        return [
            "emergency" => ["emergency", "emergency"],
            "alert" => ["alert", "alert"],
            "critical" => ["critical", "critical"],
            "error" => ["error", "error"],
            "warning" => ["warning", "warning"],
            "notice" => ["notice", "notice"],
            "info" => ["info", "info"],
            "debug" => ["debug", "debug"],
        ];
    }

}