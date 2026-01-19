<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\ApiKey\ApiKeyRepository;
use App\Infrastructure\RedisClient;
use App\RateLimiting\FixedWindowLimiter;

class RateLimitMiddleware implements MiddlewareInterface
{
    private ApiKeyRepository $apiKeys;
    private FixedWindowLimiter $limiter;

    public function __construct()
    {
        $this->apiKeys = new ApiKeyRepository();
        $this->limiter = new FixedWindowLimiter(
            new RedisClient()
        );
    }

    public function handle(Request $request, callable $next): Response
    {
        $apiKey = $this->extractApiKey($request);

        if (!$apiKey) {
            return $this->unauthorized('Missing API key');
        }

        $apiKeyHash = hash('sha256', $apiKey);

        $limit = 60;
        $windowSeconds = 60;

        $result = $this->limiter->check(
            apiKeyHash: $apiKeyHash,
            limit: $limit,
            windowSeconds: $windowSeconds
        );

        if (!$result->allowed) {
            return $this->rateLimited($result);
        }

        $response = $next($request);

        return $this->withRateLimitHeaders($response, $result);
    }

    // public function handle(Request $request, callable $next): Response
    // {
    //     $apiKey = $this->extractApiKey($request);

    //     if (!$apiKey) {
    //         return $this->unauthorized('Missing API key');
    //     }

    //     $apiKeyHash = hash('sha256', $apiKey);
    //     $key = $this->apiKeys->findByKeyHash($apiKeyHash);

    //     // if (!$key || !$key->active) {
    //     //     return $this->unauthorized('Invalid or revoked API key');
    //     // }
    //     if (empty($apiKey)) {
    //         return $this->unauthorized('Missing API key');
    //     }


    //     $result = $this->limiter->check(
    //         apiKeyHash: $key->keyHash,
    //         limit: $key->rateLimit,
    //         windowSeconds: $key->windowSeconds
    //     );

    //     if (!$result->allowed) {
    //         return $this->rateLimited($result);
    //     }

    //     $response = $next($request);

    //     return $this->withRateLimitHeaders($response, $result);
    // }

    private function extractApiKey(Request $request): ?string
    {
        return $request->headers['X-API-Key'] ?? null;
    }

    private function unauthorized(string $message): Response
    {
        return new Response(
            401,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => $message])
        );
    }

    private function rateLimited($result): Response
    {
        return new Response(
            429,
            [
                'Content-Type' => 'application/json',
                'Retry-After' => (string) max(0, $result->resetAt - time()),
                'X-RateLimit-Remaining' => '0',
                'X-RateLimit-Reset' => (string) $result->resetAt,
            ],
            json_encode([
                'error' => 'Rate limit exceeded',
                'reset_at' => $result->resetAt
            ])
        );
    }

    private function withRateLimitHeaders(Response $response, $result): Response
    {
        return new Response(
            200,
            array_merge(
                [
                    'X-RateLimit-Remaining' => (string) $result->remaining,
                    'X-RateLimit-Reset' => (string) $result->resetAt,
                ],
                []
            ),
            $responseBody = $this->extractBody($response)
        );
    }

    private function extractBody(Response $response): string
    {
        ob_start();
        $response->send();
        return ob_get_clean();
    }
}
