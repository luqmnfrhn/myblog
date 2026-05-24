<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <div class="mb-8">
            <p class="mb-1 text-sm text-stone-600">Reading circle for</p>
            <a href="{{ route('posts.show', $circle->post) }}" class="font-serif text-2xl font-semibold hover:text-accent">{{ $circle->post->title }}</a>
            <h1 class="mt-2 text-lg text-stone-600">{{ $circle->name }}</h1>
            <p class="mt-3 text-sm text-stone-600">{{ $circle->members->count() }} {{ \Illuminate\Support\Str::plural('member', $circle->members->count()) }}</p>
        </div>

        <div class="mb-8 max-h-96 space-y-4 overflow-y-auto">
            @foreach ($circle->messages->reverse() as $message)
                <div class="rounded-md bg-white p-4 shadow-sm ring-1 ring-stone-200">
                    <div class="mb-1 flex items-center gap-2">
                        <span class="text-sm font-medium text-stone-700">{{ $message->author->name }}</span>
                        <span class="text-xs text-stone-600">{{ $message->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm leading-7 text-stone-600">{{ $message->body }}</p>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('circles.messages.store', $circle) }}" class="border-t border-stone-200 pt-4">
            @csrf
            <div class="flex gap-3">
                <input name="body" type="text" placeholder="Share your thoughts..." class="accessible-field flex-1 rounded-md px-4 py-2 text-sm focus:outline-none">
                <button type="submit" class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-700">Send</button>
            </div>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
        </form>
    </div>
</x-app-layout>
