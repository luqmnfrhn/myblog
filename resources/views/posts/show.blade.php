<x-authenticated-layout>
    <article class="mx-auto max-w-2xl">
        <a href="{{ route('posts.index') }}" class="text-sm text-stone-600 hover:text-accent">Back to stories</a>

        <header class="mb-8 mt-8">
            <h1 class="font-serif text-4xl font-semibold leading-tight text-stone-950 sm:text-5xl">{{ $post->title }}</h1>

            <div class="mt-5 flex flex-wrap items-center gap-3 text-sm text-stone-500">
                @if ($post->author)
                    <a href="{{ route('writers.show', $post->author) }}" class="font-medium text-stone-700 hover:text-accent">{{ $post->author->name }}</a>
                    <span>/</span>
                @endif
                <span>{{ $post->published_at?->format('d M Y') }}</span>
                <span>/</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
        </header>

        <p class="mb-8 text-lg leading-8 text-stone-600">{{ $post->excerpt }}</p>

        <div class="story-body">
            @foreach (preg_split('/\n\n+/', trim($post->body)) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        @auth
            <div class="mt-10 flex flex-wrap gap-3">
                @foreach (\App\Enums\ReactionType::cases() as $type)
                    @php
                        $count = $post->reactions->where('type', $type)->count();
                        $reacted = $post->reactions->where('user_id', auth()->id())->where('type', $type)->isNotEmpty();
                    @endphp

                    <form method="POST" action="{{ route('posts.reactions.store', $post) }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type->value }}">
                        <button type="submit" class="{{ $reacted ? 'border-accent bg-accent text-white' : 'border-stone-200 text-stone-600 hover:border-accent hover:text-accent' }} rounded-md border px-3 py-2 text-sm transition">
                            {{ $type->label() }}@if ($count > 0) / {{ $count }}@endif
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="mt-8 border-t border-stone-200 pt-8">
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center gap-2 text-sm text-stone-500 hover:text-stone-900">
                        <span>Start a reading circle</span>
                        <span class="transition-transform group-open:rotate-180">v</span>
                    </summary>
                    <form method="POST" action="{{ route('posts.circles.store', $post) }}" class="mt-3 flex gap-3">
                        @csrf
                        <input name="name" type="text" placeholder="Name your circle..." class="accessible-field flex-1 rounded-md px-4 py-2 text-sm focus:outline-none">
                        <button type="submit" class="rounded-md bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-700">Create</button>
                    </form>
                </details>
            </div>
        @endauth

        @if ($post->circles->isNotEmpty())
            <section class="mt-8 border-t border-stone-200 pt-8">
                <h2 class="mb-4 font-serif text-xl font-semibold">Reading circles</h2>
                <div class="space-y-3">
                    @foreach ($post->circles as $circle)
                        <div class="flex items-center justify-between gap-4 rounded-md border border-stone-200 bg-white p-4">
                            <div>
                                <p class="font-medium text-stone-800">{{ $circle->name }}</p>
                                <p class="text-sm text-stone-600">{{ $circle->created_at->diffForHumans() }}</p>
                            </div>
                            @auth
                                @if ($circle->members->contains(auth()->user()))
                                    <a href="{{ route('circles.show', $circle) }}" class="text-sm font-medium text-accent">Open</a>
                                @else
                                    <form method="POST" action="{{ route('circles.join', $circle) }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-accent">Join</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="mt-16 border-t border-stone-200 pt-8">
            <h2 class="mb-6 font-serif text-2xl font-semibold">Responses</h2>

            @auth
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-8">
                    @csrf
                    <textarea name="body" rows="3" placeholder="What do you think?" class="accessible-field w-full resize-none rounded-md px-4 py-3 text-sm focus:outline-none">{{ old('body') }}</textarea>
                    <button type="submit" class="mt-2 rounded-md bg-stone-900 px-5 py-2 text-sm text-white hover:bg-stone-700">Respond</button>
                </form>
            @endauth

            <div class="space-y-6">
                @forelse ($post->comments as $comment)
                    <div class="border-b border-stone-200 pb-6">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-sm font-medium text-stone-700">{{ $comment->author->name }}</span>
                            <span class="text-xs text-stone-600">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm leading-7 text-stone-600">{{ $comment->body }}</p>

                        @foreach ($comment->replies as $reply)
                            <div class="ml-6 mt-4 border-t border-stone-100 pt-4">
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="text-sm font-medium text-stone-700">{{ $reply->author->name }}</span>
                                    <span class="text-xs text-stone-600">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm leading-7 text-stone-600">{{ $reply->body }}</p>
                            </div>
                        @endforeach

                        @auth
                            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <input name="body" type="text" placeholder="Reply..." class="w-full border-0 border-b border-stone-300 bg-transparent pb-2 text-sm text-stone-950 placeholder:text-stone-500 caret-stone-950 focus:border-accent focus:ring-0">
                            </form>
                        @endauth
                    </div>
                @empty
                    <p class="text-sm text-stone-600">No responses yet.</p>
                @endforelse
            </div>
        </section>
    </article>
</x-authenticated-layout>
