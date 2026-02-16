# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 REST API for a golf wedge yardage matrix application. Pure JSON API consumed by a separate SPA at `wedgematrix.ca`. Users register/login and manage a personal wedge matrix (club yardage grid with carry/total values).

## Common Commands

| Task | Command |
|---|---|
| First-time setup | `composer run setup` |
| Start dev environment | `composer run dev` |
| Run all tests | `./vendor/bin/sail artisan test` |
| Run a single test | `./vendor/bin/sail artisan test --filter=TestClassName` |
| Code style (lint/fix) | `./vendor/bin/pint` |
| Run migrations | `php artisan migrate` |
| Sail (Docker dev) | `./vendor/bin/sail up` |

Tests run inside the Sail container via `./vendor/bin/sail artisan test`. Tests use a `testing` MySQL database with `RefreshDatabase`.

## Architecture

**Service → Repository → Model pattern:**
- **Controllers** are thin, delegating to services. Single-action controllers (`__invoke()`) for login, register, and user endpoints.
- **Services** (`app/Services/`) contain business logic (e.g., `UserCreationService` creates a user + default wedge matrix in one operation).
- **Repositories** (`app/Repositories/`) encapsulate Eloquent queries, scoped by authenticated user.
- **Form Requests** (`app/Http/Requests/`) handle all validation.
- **API Resources** (`app/Http/Resources/`) shape JSON responses.
- **Custom Exceptions** (`app/Exceptions/`) are thrown from services, caught in controllers, and translated to JSON error responses.

**Auth:** Laravel Sanctum bearer tokens. Token name: `'wedge-matrix'`. Three routes are protected behind `auth:sanctum` middleware. Ownership is checked manually in controllers (no policies/gates).

**Data model:** Each `User` has exactly one `WedgeMatrix` (one-to-one). The matrix stores `column_headers` and `yardage_values` as JSON columns. Default column headers are `['25%', '50%', '75%', '100%']`, set at registration.

## API Routes (all prefixed `/api`)

- `POST /register`, `POST /login` — public
- `GET /user` — authenticated, returns user + matrix
- `GET /wedge-matrix`, `PUT /wedge-matrix/{wedgeMatrix}` — authenticated
- `GET /up` — health check

## Testing

Tests live in `tests/Feature/` only (no unit tests). Test directory mirrors `app/` structure: `Controllers/`, `Repositories/`, `Services/`. Tests use `RefreshDatabase` trait and Mockery for mocking.

## CI/CD

- **PR checks** (`.github/workflows/check.yml`): MySQL 8.0 service, PHP 8.2, runs migrations + test suite.
- **Deploy** (`.github/workflows/deploy.yml`): On push to `main`, builds Docker image via `docker/php/Dockerfile` (PHP 8.4-fpm + Nginx) and pushes to `ghcr.io`.

## Environment

- PHP 8.2+, MySQL 8.4 (dev), MySQL 8.0 (CI)
- CORS allows `wedgematrix.ca` and `localhost:5173`
- `compose.yaml` uses Laravel Sail with MySQL service