<x-app-layout>
    <div class="space-y-10">
        @forelse ($posts as $post)
            <article class="border-b border-stone-200 pb-10">
                <div class="mb-3 flex flex-wrap items-center gap-2 text-sm text-stone-500">
                    @if ($post->is_featured)
                        <span class="rounded-md border border-accent px-2 py-0.5 text-xs font-medium text-accent">Featured</span>
                    @endif

                    @if ($post->author)
                        <a href="{{ route('writers.show', $post->author) }}" class="font-medium text-stone-700 hover:text-accent">{{ $post->author->name }}</a>
                        <span class="text-stone-300">/</span>
                    @endif

                    <span>{{ $post->published_at?->format('d M Y') }}</span>
                </div>

                <h2 class="mb-2 font-serif text-3xl font-semibold leading-snug">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">{{ $post->title }}</a>
                </h2>

                <p class="mb-4 max-w-2xl leading-7 text-stone-600">{{ $post->excerpt }}</p>

                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('posts.show', $post) }}" class="text-sm font-medium text-accent hover:text-accent-light">Read more</a>

                    @auth
                        @if (auth()->user()->is_admin)
                            <form method="POST" action="{{ route('admin.posts.feature', $post) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-stone-400 hover:text-accent">{{ $post->is_featured ? 'Unfeature' : 'Feature' }}</button>
                            </form>
                        @endif
                    @endauth
                </div>
            </article>
        @empty
            <p class="text-stone-500">No stories yet.</p>
        @endforelse
    </div>
</x-app-layout>
