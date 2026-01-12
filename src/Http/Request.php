<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    public function __construct(
        public array $headers,
        public array $query,
        public array $body,
        public string $method,
        public string $uri
    ) {}

    public static function fromGlobals(): self
    {
        return new self(
            getallheaders(),
            $_GET,
            $_POST,
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
        );
    }
}
