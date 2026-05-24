@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-stone-950">Sign in</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="checkbox" name="remember" class="rounded border-stone-300 text-accent focus:ring-accent">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-accent hover:text-accent-light">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full rounded-md bg-stone-900 px-4 py-2 font-semibold text-white hover:bg-stone-700">
            Sign in
        </button>
    </form>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-stone-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-stone-50 px-2 text-stone-500">Or continue with</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <a href="{{ route('auth.social.redirect', 'google') }}"
                class="flex items-center justify-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-800 hover:border-accent hover:text-accent">
                Google
            </a>
            <a href="{{ route('auth.social.redirect', 'github') }}"
                class="flex items-center justify-center rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-medium text-stone-800 hover:border-accent hover:text-accent">
                GitHub
            </a>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-stone-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-accent hover:text-accent-light">Register</a>
    </p>
</div>
@endsection
