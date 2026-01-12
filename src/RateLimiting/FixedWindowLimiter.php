<?php

declare(strict_types=1);

namespace App\RateLimiting;

use App\Infrastructure\RedisClient;

class FixedWindowLimiter implements RateLimiterInterface
{
    public function __construct(
        private RedisClient $redisClient
    ) {}

    public function check(
        string $apiKeyHash,
        int $limit,
        int $windowSeconds
    ): RateLimitResult {
        $now = time();
        $windowStart = intdiv($now, $windowSeconds) * $windowSeconds;

        $key = sprintf('rate:%s:%d', $apiKeyHash, $windowStart);

        $redis = $this->redisClient->getClient();

        $current = $redis->incr($key);

        if ($current === 1) {
            $redis->expire($key, $windowSeconds + 5);
        }

        $remaining = max(0, $limit - $current);
        $allowed = $current <= $limit;
        $resetAt = $windowStart + $windowSeconds;

        return new RateLimitResult(
            allowed: $allowed,
            remaining: $remaining,
            resetAt: $resetAt
        );
    }
}
