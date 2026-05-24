<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? 'Nukilan, a Malaysian and Southeast Asian writing platform.' }}">

    <title>{{ $title ?? 'Nukilan' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-50 font-sans text-stone-900 antialiased">
    @include('layouts.navigation')

    @isset($header)
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto max-w-4xl px-5 py-6">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main class="mx-auto w-full max-w-4xl px-5 py-10">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>
</body>

</html>
