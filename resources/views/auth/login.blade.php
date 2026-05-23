@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-white">Sign in</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm text-stone-300">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-stone-300">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-stone-300">
                <input type="checkbox" name="remember" class="rounded border-white/20">
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-amber-400 hover:text-amber-300">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit"
            class="w-full rounded-lg bg-amber-400 px-4 py-2 font-semibold text-stone-950 hover:bg-amber-300">
            Sign in
        </button>
    </form>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/10"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-stone-950 px-2 text-stone-400">Or continue with</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <a href="{{ route('auth.social.redirect', 'google') }}"
                class="flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white hover:bg-white/10">
                Google
            </a>
            <a href="{{ route('auth.social.redirect', 'github') }}"
                class="flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white hover:bg-white/10">
                GitHub
            </a>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-stone-400">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-amber-400 hover:text-amber-300">Register</a>
    </p>
</div>
@endsection
