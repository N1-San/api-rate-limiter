<?php

declare(strict_types=1);

namespace App\ApiKey;

class ApiKey
{
    public function __construct(
        public string $keyHash,
        public int $rateLimit,
        public int $windowSeconds,
        public bool $active
    ) {}
}
