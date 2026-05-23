# Auth System Design

**Date:** 2026-05-23
**Status:** Approved

## Summary

Two auth flows — reader (email/password + OAuth) and admin (separate login, same guard) — sharing one `User` model with an `is_admin` flag.

## Stack

- Laravel Breeze (Blade) — email/password scaffolding
- `laravel/socialite` — Google + GitHub OAuth
- Laravel's built-in `web` guard

## Database Changes

### `users` table — add column

```php
$table->boolean('is_admin')->default(false);
```

### `social_accounts` table — new

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `user_id` | foreignId | constrained, cascadeOnDelete |
| `provider` | string | `google` or `github` |
| `provider_id` | string | provider's user ID |
| `token` | text, nullable | OAuth access token |
| `created_at` | timestamp | |

## Components

### Controllers

| Controller | Purpose |
|---|---|
| `App\Http\Controllers\Auth\*` | Breeze: login, register, password reset |
| `App\Http\Controllers\Auth\SocialController` | `redirect()` + `callback()` for Google/GitHub |
| `App\Http\Controllers\Admin\Auth\LoginController` | Admin-only login form + handler |

### Models

| Model | Relationships |
|---|---|
| `User` | `hasMany(SocialAccount)` |
| `SocialAccount` | `belongsTo(User)` |

### Middleware

| Middleware | Purpose |
|---|---|
| `EnsureIsAdmin` | Redirects non-admins away from `/admin/*` |

## Routes

```
# Reader auth (Breeze)
GET  /login                     → show login form
POST /login                     → authenticate
GET  /register                  → show register form
POST /register                  → create account
POST /logout                    → logout

# Social auth
GET  /auth/{provider}           → Socialite redirect (google|github)
GET  /auth/{provider}/callback  → Socialite callback

# Admin auth
GET  /admin/login               → show admin login form
POST /admin/login               → authenticate admin
```

All `/admin/*` routes protected by `EnsureIsAdmin` middleware.

## Socialite Flow

1. User hits `/auth/google` → redirected to Google consent
2. Callback returns with profile (email, provider ID)
3. Find existing `SocialAccount` by provider + provider_id
4. If found → log in linked `User`
5. If not found → find `User` by email or create one → create `SocialAccount` → log in

## Security

- No public admin registration — admin seeded via `DatabaseSeeder`
- `throttle:6,1` on both `/login` and `/admin/login`
- `EnsureIsAdmin` redirects non-admin to `/` with 403
- Socialite callback does not trust provider email for admin elevation

## Testing

- Feature tests for: register, login, logout, social callback (mock Socialite), admin login, admin middleware gate
- Factory state `User::factory()->admin()` for `is_admin = true`
