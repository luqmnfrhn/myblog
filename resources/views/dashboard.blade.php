<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">Dashboard</h1>
    <p class="mt-4 text-stone-500">Welcome back, {{ Auth::user()->name }}.</p>
</x-authenticated-layout>
