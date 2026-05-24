# Sidebar Navigation + Search Header Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add a fixed left sidebar (desktop) and bottom tab bar (mobile) for authenticated users, plus a search field in the top header, without touching guest/public pages.

**Architecture:** Create a new `layouts/authenticated.blade.php` layout alongside the existing `layouts/app.blade.php`. Authenticated views (`writer/posts/*`, `profile/edit`, `dashboard`) switch to `<x-authenticated-layout>`. Placeholder routes/views are added for Library, Stats, and Search. Public pages remain on `<x-app-layout>`.

**Tech Stack:** Laravel 13, PHP 8.4, Blade components, Alpine.js (already included), Tailwind CSS v4

---

### Task 1: Create the `authenticated` Blade layout component

**Files:**
- Create: `resources/views/components/authenticated-layout.blade.php`
- Create: `resources/views/layouts/authenticated.blade.php`

**Step 1: Create the layout view**

Create `resources/views/layouts/authenticated.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Nukilan' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar: desktop only --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-stone-200 bg-white sm:flex">
            <div class="flex h-16 items-center px-6 border-b border-stone-200">
                <a href="{{ route('posts.index') }}" class="font-serif text-2xl font-semibold text-stone-900">Nukilan</a>
            </div>

            <nav class="flex flex-1 flex-col gap-1 p-4">
                <x-sidebar-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/></svg>
                    </x-slot>
                    Home
                </x-sidebar-nav-link>

                <x-sidebar-nav-link :href="route('library.index')" :active="request()->routeIs('library.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </x-slot>
                    Library
                </x-sidebar-nav-link>

                <x-sidebar-nav-link :href="route('writers.show', Auth::user())" :active="request()->routeIs('writers.show') && request()->route('writer')?->is(Auth::user())">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </x-slot>
                    Profile
                </x-sidebar-nav-link>

                <x-sidebar-nav-link :href="route('writer.posts.index')" :active="request()->routeIs('writer.posts.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    </x-slot>
                    Stories
                </x-sidebar-nav-link>

                <x-sidebar-nav-link :href="route('stats.index')" :active="request()->routeIs('stats.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </x-slot>
                    Stats
                </x-sidebar-nav-link>
            </nav>

            <div class="border-t border-stone-200 p-4">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-stone-200 text-sm font-medium text-stone-700">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="truncate text-sm font-medium text-stone-800">{{ Auth::user()->name }}</span>
                </div>
                <div class="flex flex-col gap-1 text-sm">
                    <a href="{{ route('profile.edit') }}" class="text-stone-500 hover:text-stone-900">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-stone-500 hover:text-stone-900">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content area (offset by sidebar on desktop) --}}
        <div class="flex flex-1 flex-col sm:ml-64">
            {{-- Top header --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-stone-200 bg-white px-5">
                {{-- Mobile: logo --}}
                <a href="{{ route('posts.index') }}" class="font-serif text-xl font-semibold text-stone-900 sm:hidden">Nukilan</a>

                <div class="flex flex-1 justify-end sm:justify-start">
                    <form method="GET" action="{{ route('search.index') }}" class="w-full max-w-sm">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input
                                type="search"
                                name="q"
                                placeholder="Search posts, writers, tags…"
                                value="{{ request('q') }}"
                                class="w-full rounded-full border border-stone-200 bg-stone-50 py-2 pl-9 pr-4 text-sm text-stone-900 placeholder:text-stone-400 focus:border-stone-400 focus:bg-white focus:outline-none focus:ring-0"
                            >
                        </div>
                    </form>
                </div>

                {{-- Desktop: write button --}}
                <a href="{{ route('writer.posts.create') }}" class="hidden text-sm font-medium text-accent hover:text-accent-light sm:block">Write</a>
            </header>

            <main class="flex-1 px-5 py-10">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Mobile bottom tab bar --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 flex border-t border-stone-200 bg-white sm:hidden">
        <x-bottom-tab :href="route('posts.index')" :active="request()->routeIs('posts.index')" label="Home">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline stroke-linecap="round" stroke-linejoin="round" points="9 22 9 12 15 12 15 22"/></svg>
        </x-bottom-tab>
        <x-bottom-tab :href="route('library.index')" :active="request()->routeIs('library.*')" label="Library">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </x-bottom-tab>
        <x-bottom-tab :href="route('writers.show', Auth::user())" :active="request()->routeIs('writers.show') && request()->route('writer')?->is(Auth::user())" label="Profile">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </x-bottom-tab>
        <x-bottom-tab :href="route('writer.posts.index')" :active="request()->routeIs('writer.posts.*')" label="Stories">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
        </x-bottom-tab>
        <x-bottom-tab :href="route('stats.index')" :active="request()->routeIs('stats.*')" label="Stats">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </x-bottom-tab>
    </nav>
</body>
</html>
```

**Step 2: Create the Blade component class wrapper**

Create `resources/views/components/authenticated-layout.blade.php`:

```blade
@props(['title' => null])

<x-slot:title>{{ $title }}</x-slot:title>
```

Wait — Laravel Blade components work differently. Instead of a wrapper, just use the layout directly. Create `resources/views/components/authenticated-layout.blade.php` as:

```blade
@props(['title' => null])

@include('layouts.authenticated', ['slot' => $slot, 'title' => $title])
```

Actually the standard approach for anonymous components acting as layouts is simpler. Create the file as:

```blade
<!DOCTYPE html>
{{-- This file intentionally empty - layout is in layouts/authenticated.blade.php --}}
{{-- Views use @extends('layouts.authenticated') or <x-authenticated-layout> --}}
```

The correct approach: `resources/views/components/authenticated-layout.blade.php` should literally contain the layout HTML (same as what's in `layouts/authenticated.blade.php`). To avoid duplication, place the full HTML in the component file and delete the layouts version.

**Revised file plan:**
- Create ONLY: `resources/views/components/authenticated-layout.blade.php` — full layout HTML with `{{ $slot }}`
- Views use: `<x-authenticated-layout>...</x-authenticated-layout>`
- No separate `layouts/authenticated.blade.php` needed

Use the full HTML from Step 1 as the content of `resources/views/components/authenticated-layout.blade.php`.

**Step 3: Run app and verify layout file exists**

```bash
php artisan route:list --name=posts.index
```
Expected: route resolves (no errors means files are parseable).

**Step 4: Commit**

```bash
git add resources/views/components/authenticated-layout.blade.php
git commit -m "feat: add authenticated layout with sidebar and bottom tab bar skeleton"
```

---

### Task 2: Create `x-sidebar-nav-link` Blade component

**Files:**
- Create: `resources/views/components/sidebar-nav-link.blade.php`

**Step 1: Create the component**

```blade
@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    @class([
        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        'bg-stone-100 text-stone-900' => $active,
        'text-stone-600 hover:bg-stone-50 hover:text-stone-900' => ! $active,
    ])
>
    <span class="shrink-0 text-stone-500 {{ $active ? 'text-stone-900' : '' }}">
        {{ $icon }}
    </span>
    {{ $slot }}
</a>
```

**Step 2: Commit**

```bash
git add resources/views/components/sidebar-nav-link.blade.php
git commit -m "feat: add sidebar-nav-link Blade component"
```

---

### Task 3: Create `x-bottom-tab` Blade component

**Files:**
- Create: `resources/views/components/bottom-tab.blade.php`

**Step 1: Create the component**

```blade
@props(['href', 'active' => false, 'label'])

<a
    href="{{ $href }}"
    @class([
        'flex flex-1 flex-col items-center justify-center gap-1 py-2 text-xs font-medium transition-colors',
        'text-accent' => $active,
        'text-stone-500 hover:text-stone-900' => ! $active,
    ])
>
    {{ $slot }}
    <span>{{ $label }}</span>
</a>
```

**Step 2: Commit**

```bash
git add resources/views/components/bottom-tab.blade.php
git commit -m "feat: add bottom-tab Blade component for mobile nav"
```

---

### Task 4: Register placeholder routes and stub views

**Files:**
- Modify: `routes/web.php` (inside auth middleware group)
- Create: `resources/views/library/index.blade.php`
- Create: `resources/views/stats/index.blade.php`
- Create: `resources/views/search/index.blade.php`

**Step 1: Add routes to `routes/web.php`**

Add these imports at the top:

```php
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SearchController;
```

Inside the `Route::middleware('auth')->group(...)` block, add:

```php
Route::get('/library', fn () => view('library.index'))->name('library.index');
Route::get('/stats', fn () => view('stats.index'))->name('stats.index');
Route::get('/search', fn () => view('search.index'))->name('search.index');
```

No controller needed for stubs — use closures.

**Step 2: Create stub views**

`resources/views/library/index.blade.php`:
```blade
<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">Library</h1>
    <p class="mt-4 text-stone-500">Coming soon.</p>
</x-authenticated-layout>
```

`resources/views/stats/index.blade.php`:
```blade
<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">Stats</h1>
    <p class="mt-4 text-stone-500">Coming soon.</p>
</x-authenticated-layout>
```

`resources/views/search/index.blade.php`:
```blade
<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">
        Search{{ request('q') ? ': ' . request('q') : '' }}
    </h1>
    <p class="mt-4 text-stone-500">Search results coming soon.</p>
</x-authenticated-layout>
```

**Step 3: Verify routes registered**

```bash
php artisan route:list --name=library,stats,search
```
Expected: all three routes appear.

**Step 4: Commit**

```bash
git add routes/web.php resources/views/library/ resources/views/stats/ resources/views/search/
git commit -m "feat: add placeholder routes and views for library, stats, and search"
```

---

### Task 5: Migrate authenticated views to `<x-authenticated-layout>`

**Files:**
- Modify: `resources/views/writer/posts/index.blade.php`
- Modify: `resources/views/writer/posts/create.blade.php`
- Modify: `resources/views/writer/posts/edit.blade.php`
- Modify: `resources/views/profile/edit.blade.php`
- Modify: `resources/views/dashboard.blade.php`

**Step 1: Update `writer/posts/index.blade.php`**

Change opening/closing tags:
- `<x-app-layout>` → `<x-authenticated-layout>`
- `</x-app-layout>` → `</x-authenticated-layout>`

Remove any `<x-slot name="header">` blocks (sidebar layout has no header slot — page titles live inline).

**Step 2: Update `writer/posts/create.blade.php`**

Same substitution: `<x-app-layout>` → `<x-authenticated-layout>`.

**Step 3: Update `writer/posts/edit.blade.php`**

Same substitution.

**Step 4: Update `profile/edit.blade.php`**

Replace `<x-app-layout>` → `<x-authenticated-layout>`.
Remove the `<x-slot name="header">` block — the "Profile" h2 heading is dropped (page has its own headings in partials, or add one inline if needed).

**Step 5: Update `dashboard.blade.php`**

Replace entire file content. The existing dashboard uses default Breeze styling. Simplify to match app style:

```blade
<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">Dashboard</h1>
    <p class="mt-4 text-stone-500">Welcome back, {{ Auth::user()->name }}.</p>
</x-authenticated-layout>
```

**Step 6: Run Pint to fix formatting**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 7: Commit**

```bash
git add resources/views/writer/ resources/views/profile/ resources/views/dashboard.blade.php
git commit -m "feat: migrate authenticated views to new sidebar layout"
```

---

### Task 6: Write feature tests

**Files:**
- Create: `tests/Feature/AuthenticatedLayoutTest.php`

**Step 1: Create test**

```bash
php artisan make:test --phpunit AuthenticatedLayoutTest
```

**Step 2: Write test cases**

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticatedLayoutTest extends TestCase
{
    #[Test]
    public function authenticated_user_sees_sidebar_nav_on_writer_posts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('writer.posts.index'));

        $response->assertOk();
        $response->assertSee('Home');
        $response->assertSee('Library');
        $response->assertSee('Stories');
        $response->assertSee('Stats');
    }

    #[Test]
    public function authenticated_user_can_access_library_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('library.index'))->assertOk()->assertSee('Library');
    }

    #[Test]
    public function authenticated_user_can_access_stats_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('stats.index'))->assertOk()->assertSee('Stats');
    }

    #[Test]
    public function search_route_renders_with_query(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('search.index', ['q' => 'hello']))
            ->assertOk()
            ->assertSee('hello');
    }

    #[Test]
    public function guest_cannot_access_library(): void
    {
        $this->get(route('library.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_access_stats(): void
    {
        $this->get(route('stats.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_access_search(): void
    {
        $this->get(route('search.index'))->assertRedirect(route('login'));
    }
}
```

**Step 3: Run tests (expect failures before Task 4 routes exist)**

```bash
php artisan test --compact tests/Feature/AuthenticatedLayoutTest.php
```

**Step 4: Fix any failures, re-run until green**

```bash
php artisan test --compact tests/Feature/AuthenticatedLayoutTest.php
```
Expected: all 7 tests pass.

**Step 5: Commit**

```bash
git add tests/Feature/AuthenticatedLayoutTest.php
git commit -m "test: add feature tests for authenticated layout and placeholder routes"
```

---

### Task 7: Verify full test suite

**Step 1: Run all tests**

```bash
php artisan test --compact
```
Expected: all existing tests still pass. If any fail, investigate before proceeding.

**Step 2: Ask user to run dev server and visually verify**

```bash
composer run dev
```

Navigate to `/writer/posts` while logged in. Verify:
- Sidebar visible on desktop
- Bottom tab bar visible on mobile (resize browser)
- Search field in header submits to `/search?q=…`
- Active nav item highlighted on each page
- Guest pages (`/`, `/posts/slug`, `/writers/name`) unchanged

---

## Notes

- The `accent` color class is already used in the existing nav — ensure it's defined in `resources/css/app.css`. If sidebar active state looks unstyled, check this.
- The `dashboard` route is at `/dashboard` — check `routes/auth.php` to confirm it's still pointing to the right view after the template change.
- Mobile bottom tab bar adds `pb-16` padding to `<main>` or the body to prevent content being hidden behind the fixed bar. Add `pb-16 sm:pb-0` to the `<main>` element in `authenticated-layout.blade.php`.
- Search route is inside `auth` middleware — unauthenticated search will redirect to login. If public search is needed later, move it outside the group.
