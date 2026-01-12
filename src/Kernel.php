<?php

declare(strict_types=1);

namespace App;

use App\Http\Request;
use App\Http\Response;
use App\Http\Middleware\RateLimitMiddleware;

class Kernel
{
    public function handle(): Response
    {
        $request = Request::fromGlobals();

        $middleware = new RateLimitMiddleware();

        return $middleware->handle($request, function ($request) {
            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['status' => 'ok'])
            );
        });
    }
}
