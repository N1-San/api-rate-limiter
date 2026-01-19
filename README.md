# API Rate Limiter (PHP + Redis)

A **framework-agnostic API rate limiter** written in plain PHP.

No Laravel. No Symfony. No magic.

Just **PHP, Redis, and well-defined boundaries**.

This project is designed for developers who care about:

* Predictable behavior under load
* Minimal setup and failure points
* Clear data models
* Easy self-hosting
* Open-source friendliness

If you can run PHP and Redis, you can run this.

---

## Why this exists

Most rate-limiting solutions are:

* Tightly coupled to frameworks
* Hidden behind service containers
* Difficult to reason about under failure
* Over-engineered for simple use cases

This project takes the opposite approach:

> **Explicit over implicit. Simple over clever. Boring over fragile.**

It is meant to be embedded:

* In internal APIs
* As an edge service in front of microservices
* As a standalone gateway
* As a learning reference for real-world system design

---

## Features

* ✅ Fixed Window rate limiting (Redis-backed)
* ✅ API key–based authentication
* ✅ Redis TTL–driven expiry (no cron jobs)
* ✅ Framework-agnostic middleware pipeline
* ✅ Predictable Redis key schema
* ✅ Zero runtime dependencies beyond Redis
* ✅ Works with PHP built-in server, Nginx, Apache, or Docker

Planned:

* Sliding Window / Token Bucket
* Per-route limits
* Burst control
* Distributed Redis support
* Metrics export (Prometheus)

---

## Architecture (High-Level)

```
┌────────────┐     ┌──────────────────┐     ┌──────────────┐
│ HTTP Client│ ──▶ │ RateLimitMiddleware│ ──▶ │ Application  │
└────────────┘     └──────────────────┘     └──────────────┘
                           │
                           ▼
                    ┌──────────────┐
                    │ Redis        │
                    │ (Atomic ops) │
                    └──────────────┘
```

### Design principles

* **Stateless PHP** – all state lives in Redis
* **Atomic Redis operations** – correctness under concurrency
* **Explicit failure modes** – no silent fallbacks
* **Readable over clever** – optimized last, understood first

---

## Redis Schema

All keys are namespaced and deterministic.

```
rl:{api_key}:{window_start}
```

Example:

```
rl:test-key-123:1705651200
```

* Value: request count (integer)
* TTL: window size (seconds)

Redis is the source of truth.

No local caches. No sync problems.

---

## Project Structure

```
api-rate-limiter/
├── public/
│   └── index.php        # Front controller
├── src/
│   ├── Http/
│   │   └── Middleware/
│   │       └── RateLimitMiddleware.php
│   ├── Infrastructure/
│   │   └── RedisClient.php
│   ├── RateLimiting/
│   │   └── FixedWindowLimiter.php
│   ├── Kernel.php       # Middleware pipeline
│   └── Response.php
├── composer.json
└── README.md
```

Nothing hidden.

If you delete a file, you know exactly what broke.

---

## Installation

### Requirements

* PHP **8.1+** (8.3 recommended)
* Redis **6+**
* Composer

### Install dependencies

```
composer install
```

### Configure Redis

Edit your environment or config:

```
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## Running locally

Using PHP’s built-in server:

```
php -S localhost:8888 -t public
```

Test:

```
curl -H "X-API-Key: test-key-123" http://localhost:8888
```

---

## API Key Handling

This project **does not** manage API key storage for you.

That is intentional.

You can:

* Hardcode keys (for internal services)
* Load from env
* Validate against Redis / DB
* Plug into an external auth service

The middleware only enforces **rate limits**, not identity semantics.

---

## Failure Modes (Explicit by Design)

| Condition           | Response                 |
| ------------------- | ------------------------ |
| Missing API key     | `401 Unauthorized`       |
| Invalid API key     | `401 Unauthorized`       |
| Rate limit exceeded | `429 Too Many Requests`  |
| Redis unavailable   | Fail-fast (configurable) |

No silent degradation.

If Redis is down, you *should know*.

---

## Why Fixed Window?

Fixed Window is:

* Easy to reason about
* Cheap in Redis
* Deterministic

Yes, it allows bursts at window edges.

That tradeoff is **explicit**, not accidental.

More advanced algorithms can be layered on later.

---

## Philosophy

This project is intentionally boring.

* No service containers
* No annotations
* No magic globals

Just code you can:

* Read in one sitting
* Debug with `var_dump`
* Trust in production

---

## Who this is for

* Backend engineers who want control
* Teams building internal platforms
* Developers learning real system design
* People tired of framework lock-in

If you want batteries included, this is not for you.

If you want **understanding**, it is.

---

## License

MIT

Use it. Break it. Fork it. Improve it.

---

## Final note

If you are reading this README and thinking:

> “This feels like something I could actually run in production”

That is the goal.
