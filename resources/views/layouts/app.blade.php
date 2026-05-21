<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $description ?? 'A simple Laravel blog' }}">

        <title>{{ $title ?? config('app.name', 'Laravel Blog') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100 antialiased">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-24 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-orange-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-8 lg:px-10">
            <header class="flex items-center justify-between gap-4 border-b border-white/10 pb-6">
                <a href="/" class="text-lg font-semibold tracking-wide text-white">{{ config('app.name', 'Laravel Blog') }}</a>
                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs uppercase tracking-[0.2em] text-stone-300">Simple blog</span>
            </header>

            <main class="flex-1 py-8">
                @yield('content')
            </main>
        </div>
    </body>
</html>