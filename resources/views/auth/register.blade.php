@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-stone-950">Create account</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-stone-700">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-stone-700">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="w-full rounded-md bg-stone-900 px-4 py-2 font-semibold text-white hover:bg-stone-700">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-stone-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-accent hover:text-accent-light">Sign in</a>
    </p>
</div>
@endsection
