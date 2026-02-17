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

    private function getExpectedMessage($levelString){
        return '[' . Carbon::now()->toDateTimeString() . '] testing.'. strtoupper($levelString) . ': ' . self::LOG_MESSAGE;
    }

    private function getLogContent(){
        return trim(File::get(storage_path('logs/plugin.log')));
    }

    public function testLog() {
        PluginLog::log(self::LOG_MESSAGE);
        $logContent = $this->getLogContent();
        $this->assertEquals($this->getExpectedMessage('info'), $logContent);
    }

    #[DataProvider('logLevelDataProvider')]
    public function testLogFunctions($level, $method) {
        PluginLog::$method(self::LOG_MESSAGE);
        $logContent = $this->getLogContent();
        $this->assertEquals($this->getExpectedMessage($level), $logContent);
    }

    public static function logLevelDataProvider(){
        return [
            "emergency" =>["emergency", "emergency"],
            "alert" =>["alert", "alert"],
            "critical" =>["critical", "critical"],
            "error" =>["error", "error"],
            "warning" =>["warning", "warning"],
            "notice" =>["notice", "notice"],
            "info" =>["info", "info"],
            "debug" =>["debug", "debug"],
        ];
    }

}