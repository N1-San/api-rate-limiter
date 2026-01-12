<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    public function __construct(
        private int $status,
        private array $headers,
        private string $body
    ) {}

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
    }
}
