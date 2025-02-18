<?php


namespace Tests;

class Permission {
    private $errorMessage;
    private $method;
    private $url;
    private $data;
    private $errorCode;

    public function __construct(string $method, string $url, string $errorMessage, array $data = [], int $errorCode = 400) {
        $this->url = $url;
        $this->method = $method;
        $this->errorMessage = $errorMessage;
        $this->data = $data;
        $this->errorCode = $errorCode;
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

    public function getErrorCode() {
        return $this->errorCode;
    }

    public static function for(string $method, string $url, string $errorMessage, array $data = [], int $errorCode = 400) {
        return [(new Permission($method, $url, $errorMessage, $data, $errorCode))];
    }

    public function provide() {
        return [
            'method'        => $this->method,
            'url'           => $this->url,
            'errorMessage'  => $this->errorMessage,
            'data'          => $this->data,
            'errorCode'     => $this->errorCode,
        ];
    }
}