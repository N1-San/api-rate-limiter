<?php

declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    public static function load(string $path): void
    {
        if (file_exists($path)) {
            Dotenv::createImmutable(dirname($path))->load();
        }
    }

    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
