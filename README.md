# PrintWorks Backend

Laravel 11 (PHP 8.3) API with PostgreSQL, Redis, Horizon, Lighthouse (GraphQL), Sanctum, S3/R2.

## Tech Stack

| Tech | Purpose |
|------|---------|
| Laravel 11 | Core framework |
| PostgreSQL | Database (orders & inventory) |
| Redis | Cache, sessions, queues |
| Laravel Horizon | Queue monitoring & workers |
| REST + GraphQL (Lighthouse) | API |
| Laravel Sanctum | Authentication |
| S3 / Cloudflare R2 | Media storage |

## Structure

```
backend/
├── app/
│   ├── Http/Controllers/
│   ├── Jobs/              # Queue jobs (Horizon)
│   ├── Models/
│   └── Providers/
├── config/
│   ├── database.php       # PostgreSQL
│   ├── filesystems.php    # S3 / R2
│   ├── horizon.php        # Queue config
│   ├── sanctum.php        # API auth
│   └── lighthouse.php     # GraphQL
├── graphql/
│   └── schema.graphql    # Lighthouse schema
├── routes/
│   ├── api.php            # REST + Sanctum
│   ├── web.php
│   └── console.php
└── database/
    ├── migrations/
    └── seeders/
```

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB (PostgreSQL), Redis, S3/R2 in .env
php artisan migrate
php artisan horizon   # or: php artisan queue:work
```

## Endpoints

- **REST** – `/api/*` (e.g. `/api/user` with Sanctum)
- **GraphQL** – `/graphql` (Lighthouse)

## Queue

- Default driver: `redis`
- Run workers: `php artisan horizon`
