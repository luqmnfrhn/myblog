# Design: Logout Modal + Guest Header CTA

**Date:** 2026-05-28  
**Status:** Approved

## Problem

1. Guest users see "Sign in" / "Get started" buried at sidebar bottom-left in `authenticated-layout` — inconsistent with logged-in avatar position (top-right header).
2. Logout is a bare text link with no confirmation — easy to trigger accidentally.

## Scope

Single file change: `resources/views/components/authenticated-layout.blade.php`

## Change 1 — Guest buttons in top-right header

Move `@guest` sign-in/register block from sidebar bottom into the top header's right-side action area.

**Before (sidebar bottom):**
```blade
@guest
<div class="border-t border-stone-200 p-4">
    <div class="flex flex-col gap-1 text-sm">
        <a href="{{ route('login') }}" class="font-medium text-stone-900 hover:text-accent">Sign in</a>
        <a href="{{ route('register') }}" class="text-stone-500 hover:text-stone-900">Get started</a>
    </div>
</div>
@endguest
```

**After (header right, inside `.flex.items-center.gap-3`):**
```blade
@auth
  [Write link] [Avatar dropdown]
@else
  <a href="{{ route('login') }}" class="hidden text-sm text-stone-600 hover:text-stone-900 sm:block">Sign in</a>
  <a href="{{ route('register') }}" class="hidden rounded-md bg-stone-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-stone-700 sm:block">Get started</a>
@endauth
```

Remove the `@guest` block from sidebar.

## Change 2 — Logout confirmation modal

**Dropdown trigger** — replace direct form submit with event dispatch:
```blade
<button type="button" @click="$dispatch('open-modal', 'logout-confirm'); open = false"
    class="block w-full px-4 py-2 text-start text-sm leading-5 text-stone-700 hover:bg-stone-100">
    Sign out
</button>
```

**Hidden form** — placed once at bottom of `<body>`, outside dropdown:
```blade
<form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf</form>
```

**Modal** — placed at bottom of `<body>` using existing `<x-modal>` component:
```blade
<x-modal name="logout-confirm" maxWidth="sm">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-stone-900">Sign out?</h2>
        <p class="mt-1 text-sm text-stone-500">You'll need to sign back in to access your account.</p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal', 'logout-confirm')"
                class="rounded-md border border-stone-200 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50">
                Cancel
            </button>
            <button type="button" onclick="document.getElementById('logout-form').submit()"
                class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">
                Sign out
            </button>
        </div>
    </div>
</x-modal>
```

## Mobile

Guest bottom tab bar already shows "Sign in" — no change. New header guest buttons use `sm:block` / `hidden` so mobile sees nothing new in header (consistent with avatar dropdown behaviour).

## Files Changed

- `resources/views/components/authenticated-layout.blade.php` — only file touched
