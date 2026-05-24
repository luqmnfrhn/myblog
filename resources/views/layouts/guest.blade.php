<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nukilan') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-stone-50 font-sans text-stone-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-5 py-10">
        <a href="{{ route('posts.index') }}" class="font-serif text-3xl font-semibold text-stone-900">Nukilan</a>

        <div class="mt-8 w-full max-w-md rounded-md border border-stone-200 bg-white p-6 shadow-sm">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
