<?php

namespace App\Console\Utils;

use Carbon\Carbon;

/**
 * Class Printer
 * @package App\Console\Utils
 * 
 * This class is used to print messages to a specific log file.
 * It provides utility functions to create formatted receipts for commands.
 */
trait Printer {
    
    protected $filehandle;
    abstract function printerLogFileName();
    private $lineLength = 64;
    
    protected function print(string $message = "") {        
        fwrite($this->filehandle, Carbon::now() . " ::> " . $message . "\n");
    }
    
    protected function startPrinter(){
        $logLocation = "./storage/logs/" . $this->printerLogFileName();
        $this->filehandle = fopen($logLocation, "w");
    }
    
    protected function closePrinter(){
        fclose($this->filehandle);
    }
    
    protected function printHeading(string $message, array|null $data = null){
        $this->print();
        if($data != null){
            $count = count($data);
            $message .= " ($count)";
        }
        
        $this->printLine($message);
    }
    
    protected function printLine(string $message = ""){
        $spaces = 3;
        if($message != ""){
            $message = str_pad($message, strlen($this->lineLength) + 2*$spaces, STR_PAD_BOTH);
        }
        
        $this->print(str_pad($message, $this->lineLength, "=", STR_PAD_BOTH));
    }
    
    protected function printSeparator(){
        $this->print("=============================================================");
        $this->print();
        $this->print();
    }
    
    protected function printSectionHeading(string $message){
        $this->print();
        $this->print();
        $this->printLine();
        $this->printLine($message);
        $this->printLine();
        $this->print();
    }
    
    protected function printError(string $message){
        $this->print("❌ $message");
    }
    
    protected function printInfo(string $message){
        $this->print("⏺️ $message");
    }
    
    protected function printCreated(string $message){
        $this->print("➕ $message");
    }
    
    protected function printExists(string $message){
        $this->print("✅ $message");
    }
    
    protected function printConnectionConfig(string $name, array $connection){
        $this->printHeading($name);
        $this->print("Host: " . $connection["host"]);
        $this->print("Port: " . $connection["port"]);
        $this->print("Database: " . $connection["database"]);
        $this->print("Username: " . $connection["username"]);
        $this->printLine();
        $this->print();
    }
}