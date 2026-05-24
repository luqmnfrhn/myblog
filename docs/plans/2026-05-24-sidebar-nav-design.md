# Sidebar Navigation + Search Header Design

**Date:** 2026-05-24  
**Status:** Approved

## Overview

Add a sidebar navigation and search field to the authenticated app experience. Guests and public pages remain unchanged. Desktop shows a fixed left sidebar; mobile shows a bottom tab bar.

## Layout Architecture

Two layouts coexist:

- `resources/views/layouts/app.blade.php` — unchanged, guests and public pages
- `resources/views/layouts/authenticated.blade.php` — new, all auth-required views

### `authenticated.blade.php` Structure

```
<body>
  <aside>            <!-- fixed left sidebar, desktop only (hidden on mobile) -->
  <div class="flex flex-col">
    <header>         <!-- top bar: logo + search field -->
    <main>           <!-- page content slot -->
  </div>
  <nav>              <!-- bottom tab bar, mobile only (hidden on desktop) -->
</body>
```

## Sidebar (Desktop)

- Fixed left, `w-64`, full height, `bg-white border-r border-stone-200`
- Top section: Nukilan logo
- Nav items (icon + label, active state highlighted with accent color):
  1. **Home** → `route('posts.index')`
  2. **Library** → `route('library.index')` (placeholder)
  3. **Profile** → `route('writers.show', auth()->user())`
  4. **Stories** → `route('writer.posts.index')`
  5. **Stats** → `route('stats.index')` (placeholder)
- Bottom section: user avatar + name, settings link, sign out form

## Header (Top Bar)

- Height `h-16`, `bg-white border-b border-stone-200`
- Left: page title / breadcrumb slot (optional `$header` slot)
- Right: search field — text input with magnifier icon
- Search submits GET to `route('search.index')` with `?q=` query param
- Search covers posts, writers, and tags (full implementation later)

## Mobile Bottom Tab Bar

- Fixed bottom, `bg-white border-t border-stone-200`
- 5 tabs matching sidebar items: Home, Library, Profile, Stories, Stats
- Icon only with small label below each tab
- Active tab highlighted with accent color
- Hidden on `sm:` and above breakpoints

## Placeholder Routes & Pages

These routes need to be registered and point to simple placeholder views:

| Route name      | URI              | View                        |
|-----------------|------------------|-----------------------------|
| `library.index` | `/library`       | "Library coming soon"       |
| `stats.index`   | `/stats`         | "Stats coming soon"         |
| `search.index`  | `/search`        | Display `?q=` param, no results yet |

All require auth middleware.

## Views Migrated to Authenticated Layout

- `resources/views/writer/posts/` — all writer views
- `resources/views/profile/` — profile edit view
- `resources/views/dashboard.blade.php`

Public views (`posts/index`, `posts/show`, `writers/show`) keep `layouts/app.blade.php`.

## Out of Scope (this iteration)

- Actual search results implementation
- Library, Stats page content
- Sidebar collapse/expand toggle
