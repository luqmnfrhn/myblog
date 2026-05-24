<x-app-layout>
    <div class="mx-auto max-w-md text-center">
        <h1 class="mb-2 font-serif text-3xl font-semibold">Support {{ $writer->name }}</h1>
        <p class="mb-8 text-stone-500">Your tip goes directly to the writer.</p>

        <form method="POST" action="{{ route('writers.tip.store', $writer) }}" class="space-y-4">
            @csrf

            <div class="flex justify-center gap-3">
                @foreach ([500, 1000, 2000] as $preset)
                    <button type="button" onclick="document.getElementById('amount').value = {{ $preset }}" class="rounded-md border border-stone-200 px-4 py-2 text-sm hover:border-accent">
                        RM {{ number_format($preset / 100, 2) }}
                    </button>
                @endforeach
            </div>

            <input id="amount" name="amount_cents" type="number" min="100" placeholder="Custom amount (cents)" class="w-full rounded-md border border-stone-200 px-4 py-2 text-center text-sm focus:border-accent focus:outline-none">
            <x-input-error :messages="$errors->get('amount_cents')" class="mt-2" />

            <button type="submit" class="w-full rounded-md bg-stone-900 py-3 text-sm font-medium text-white hover:bg-stone-700">Send tip via Stripe</button>
        </form>
    </div>
</x-app-layout>
