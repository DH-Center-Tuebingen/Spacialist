<?php


namespace Tests;

class Permission {
    private $errorMessage;
    private $method;
    private $url;
    private $data;
    
    public function __construct(string $method, string $url,string $errorMessage, array $data = []) {
        $this->url = $url;
        $this->method = $method;
        $this->errorMessage = $errorMessage;
        $this->data = $data;
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

    public function getData() {
        return $this->data;
    }
    
    public static function for(string $method, string $url, string $errorMessage, array $data = []) {
        return [(new Permission($method, $url, $errorMessage, $data))];
    }
    
    public function provide(){
        return [
            'method'        => $this->method,
            'url'           => $this->url,
            'errorMessage'  => $this->errorMessage,
            'data'          => $this->data,
        ];
    }
}