<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Redis;
use App\Config\Config;

class RedisClient
{
    private Redis $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect(
            Config::get('REDIS_HOST', '127.0.0.1'),
            (int) Config::get('REDIS_PORT', 6379)
        );
    }

    public function getClient(): Redis
    {
        return $this->redis;
    }
}
