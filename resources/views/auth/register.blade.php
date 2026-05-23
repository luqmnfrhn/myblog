@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-white">Create account</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm text-stone-300">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm text-stone-300">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-stone-300">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm text-stone-300">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full rounded-lg bg-amber-400 px-4 py-2 font-semibold text-stone-950 hover:bg-amber-300">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-400">
        Already have an account?
        <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300">Sign in</a>
    </p>
</div>
@endsection
