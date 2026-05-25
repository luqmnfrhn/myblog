<x-authenticated-layout>
    <h1 class="font-serif text-3xl font-semibold">
        Search{{ request('q') ? ': ' . request('q') : '' }}
    </h1>
    <p class="mt-4 text-stone-500">Search results coming soon.</p>
</x-authenticated-layout>
