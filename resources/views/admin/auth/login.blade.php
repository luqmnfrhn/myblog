@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-stone-950">Admin Login</h1>

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-stone-700">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-stone-700">Password</label>
            <input type="password" id="password" name="password" required
                class="accessible-field mt-1 w-full rounded-md px-4 py-2 focus:outline-none">
        </div>

        <button type="submit"
            class="w-full rounded-md bg-stone-900 px-4 py-2 font-semibold text-white hover:bg-stone-700">
            Sign in
        </button>
    </form>
</div>
@endsection
