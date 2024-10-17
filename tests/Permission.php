<?php


namespace Tests;

class Permission {
    private $errorMessage;
    private $method;
    private $url;
    
    public function __construct(string $method, string $url,string $errorMessage) {
        $this->url = $url;
        $this->method = $method;
        $this->errorMessage = $errorMessage;
    }
    
    public function getErrorMessage() {
        return $this->errorMessage;
    }
    
    public function getMethod() {
        return $this->method;
    }
    
    public function getUrl() {
        return $this->url;
    }
    
    public static function for(string $method, string $url, string $errorMessage){
        return [(new Permission($method, $url, $errorMessage))];
    }
    
    public function provide(){
        return [
            'method' => $this->method,
            'url' => $this->url,
            'errorMessage' => $this->errorMessage
        ];
    }
}