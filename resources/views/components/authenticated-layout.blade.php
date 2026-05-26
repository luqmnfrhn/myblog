<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Nukilan' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    @php $authUser = auth()->user(); @endphp
    <div class="flex min-h-screen">
        {{-- Sidebar: desktop only --}}
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-stone-200 bg-white sm:flex">
            <div class="flex h-16 items-center border-b border-stone-200 px-6">
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

                @auth
                <x-sidebar-nav-link :href="route('writers.show', $authUser)" :active="request()->routeIs('writers.show') && request()->route('writer')?->is($authUser)">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </x-slot>
                    Profile
                </x-sidebar-nav-link>
                @endauth

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

            @guest
            <div class="border-t border-stone-200 p-4">
                <div class="flex flex-col gap-1 text-sm">
                    <a href="{{ route('login') }}" class="font-medium text-stone-900 hover:text-accent">Sign in</a>
                    <a href="{{ route('register') }}" class="text-stone-500 hover:text-stone-900">Get started</a>
                </div>
            </div>
            @endguest
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
                                aria-label="Search posts, writers, tags"
                                value="{{ request('q') }}"
                                class="w-full rounded-full border border-stone-200 bg-stone-50 py-2 pl-9 pr-4 text-sm text-stone-900 placeholder:text-stone-400 focus:border-stone-400 focus:bg-white focus:outline-none focus:ring-0"
                            >
                        </div>
                    </form>
                </div>

                {{-- Right side: write + avatar dropdown --}}
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
                            <div class="px-4 py-2 border-b border-stone-100">
                                <p class="truncate text-sm font-medium text-stone-900">{{ $authUser->name }}</p>
                                <p class="truncate text-xs text-stone-500">{{ $authUser->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')">Settings</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Sign out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                    @endauth
                </div>
            </header>

            <main class="flex-1 px-5 py-10 pb-20 sm:pb-10">
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
        @auth
        <x-bottom-tab :href="route('writers.show', $authUser)" :active="request()->routeIs('writers.show') && request()->route('writer')?->is($authUser)" label="Profile">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </x-bottom-tab>
        @else
        <x-bottom-tab :href="route('login')" :active="false" label="Sign in">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </x-bottom-tab>
        @endauth
        <x-bottom-tab :href="route('writer.posts.index')" :active="request()->routeIs('writer.posts.*')" label="Stories">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
        </x-bottom-tab>
        <x-bottom-tab :href="route('stats.index')" :active="request()->routeIs('stats.*')" label="Stats">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </x-bottom-tab>
    </nav>
</body>
</html>
