<?php

declare(strict_types=1);

namespace App\RateLimiting;

interface RateLimiterInterface
{
    public function check(string $apiKeyHash, int $limit, int $windowSeconds): RateLimitResult;
}
