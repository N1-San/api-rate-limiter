<?php

declare(strict_types=1);

namespace App\ApiKey;

class ApiKeyRepository
{
    public function findByKeyHash(string $keyHash): ?ApiKey
    {
        if ($keyHash === hash('sha256', 'test-api-key')) {
            return new ApiKey(
                keyHash: $keyHash,
                rateLimit: 100,
                windowSeconds: 60,
                active: true
            );
        }

        return null;
    }
}
