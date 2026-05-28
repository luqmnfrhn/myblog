# Logout Modal + Guest Header CTA Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Move guest sign-in/register buttons to top-right header and add a confirmation modal before logout in `authenticated-layout`.

**Architecture:** Single file change to `authenticated-layout.blade.php` — move `@guest` sidebar block into header, replace logout form submit with Alpine event dispatch, add hidden form + `<x-modal>` at body bottom.

**Tech Stack:** Laravel Blade, Alpine.js, Tailwind CSS v4, existing `<x-modal>` and `<x-dropdown>` components.

---

### Task 1: Move guest buttons from sidebar to header

**Files:**
- Modify: `resources/views/components/authenticated-layout.blade.php:64-71` (remove sidebar guest block)
- Modify: `resources/views/components/authenticated-layout.blade.php:98-123` (update header right section)

**Step 1: Remove sidebar `@guest` block**

In `authenticated-layout.blade.php`, delete lines 64–71:

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

**Step 2: Update header right section**

Replace the existing header right block (lines ~98–124):

```blade
{{-- Right side: write + avatar dropdown (auth) OR sign-in/register (guest) --}}
<div class="flex items-center gap-3">
    @auth
    <a href="{{ route('writer.posts.create') }}" class="hidden text-sm font-medium text-accent hover:text-accent-light sm:block">Write</a>

    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-full bg-stone-200 text-sm font-medium text-stone-700 hover:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-stone-400 focus:ring-offset-2" aria-label="Account menu">
                {{ mb_substr($authUser->name, 0, 1) }}
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="border-b border-stone-100 px-4 py-2">
                <p class="truncate text-sm font-medium text-stone-900">{{ $authUser->name }}</p>
                <p class="truncate text-xs text-stone-500">{{ $authUser->email }}</p>
            </div>
            <x-dropdown-link :href="route('profile.edit')">Settings</x-dropdown-link>
            <button type="button"
                @click="$dispatch('open-modal', 'logout-confirm'); open = false"
                class="block w-full px-4 py-2 text-start text-sm leading-5 text-stone-700 hover:bg-stone-100 focus:bg-stone-100 focus:outline-none transition duration-150 ease-in-out">
                Sign out
            </button>
        </x-slot>
    </x-dropdown>
    @else
    <a href="{{ route('login') }}" class="hidden text-sm text-stone-600 hover:text-stone-900 sm:block">Sign in</a>
    <a href="{{ route('register') }}" class="hidden rounded-md bg-stone-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-stone-700 sm:block">Get started</a>
    @endauth
</div>
```

**Step 3: Verify visually**

Run `npm run dev` (or `composer run dev`) and check:
- Guest: top-right shows "Sign in" + "Get started" on desktop, nothing on mobile (sidebar still there but no bottom block)
- Auth: unchanged avatar dropdown

**Step 4: Commit**

```bash
git add resources/views/components/authenticated-layout.blade.php
git commit -m "feat: move guest sign-in/register buttons to top-right header"
```

---

### Task 2: Add logout confirmation modal

**Files:**
- Modify: `resources/views/components/authenticated-layout.blade.php` (add form + modal before `</body>`)

**Step 1: Add hidden logout form + modal at end of body**

Before the closing `</body>` tag (after the mobile bottom nav `</nav>`), add:

```blade
{{-- Logout form (triggered by modal confirm) --}}
<form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf</form>

{{-- Logout confirmation modal --}}
<x-modal name="logout-confirm" maxWidth="sm">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-stone-900">Sign out?</h2>
        <p class="mt-1 text-sm text-stone-500">You'll need to sign back in to access your account.</p>
        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                @click="$dispatch('close-modal', 'logout-confirm')"
                class="rounded-md border border-stone-200 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50 focus:outline-none">
                Cancel
            </button>
            <button
                type="button"
                onclick="document.getElementById('logout-form').submit()"
                class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700 focus:outline-none">
                Sign out
            </button>
        </div>
    </div>
</x-modal>
```

**Step 2: Test modal behaviour**

Manual checks:
1. Click avatar dropdown → click "Sign out" → modal appears with title "Sign out?" and two buttons
2. Click "Cancel" → modal closes, user stays logged in
3. Press Escape → modal closes
4. Click backdrop → modal closes
5. Click "Sign out" → form submits, user is logged out and redirected

**Step 3: Run Pint to fix any formatting**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 4: Commit**

```bash
git add resources/views/components/authenticated-layout.blade.php
git commit -m "feat: add logout confirmation modal"
```
