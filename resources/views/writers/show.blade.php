<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <div class="mb-10 border-b border-stone-200 pb-10">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-serif text-4xl font-semibold">{{ $writer->name }}</h1>
                    <p class="mt-2 text-sm text-stone-500">{{ $posts->count() }} {{ \Illuminate\Support\Str::plural('story', $posts->count()) }}</p>
                </div>

                @auth
                    @if (auth()->id() !== $writer->id)
                        <div class="flex gap-2">
                            @if ($isFollowing)
                                <form method="POST" action="{{ route('writers.unfollow', $writer) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-stone-200 px-3 py-2 text-sm text-stone-500 hover:border-stone-400">Following</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('writers.follow', $writer) }}">
                                    @csrf
                                    <button type="submit" class="rounded-md bg-stone-900 px-3 py-2 text-sm text-white hover:bg-stone-700">Follow</button>
                                </form>
                            @endif

                            <a href="{{ route('writers.tip', $writer) }}" class="rounded-md border border-stone-200 px-3 py-2 text-sm text-stone-500 hover:border-accent hover:text-accent">Tip writer</a>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <div class="space-y-8">
            @forelse ($posts as $post)
                <article class="border-b border-stone-200 pb-8">
                    <h2 class="mb-2 font-serif text-2xl font-semibold">
                        <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">{{ $post->title }}</a>
                    </h2>
                    <p class="mb-2 leading-7 text-stone-600">{{ $post->excerpt }}</p>
                    <span class="text-xs text-stone-600">{{ $post->published_at?->format('d M Y') }}</span>
                </article>
            @empty
                <p class="text-stone-600">No stories yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
