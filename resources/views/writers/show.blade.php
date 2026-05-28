<x-authenticated-layout>
    <div class="mx-auto max-w-5xl px-4">
        <div class="flex gap-12">
            {{-- Left column --}}
            <div class="min-w-0 flex-1">
                {{-- Header: name + three-dots --}}
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="truncate font-serif text-4xl font-semibold">
                        {{ $writer->name }}
                    </h1>
                    <div class="relative">
                        <button
                            onclick="this.nextElementSibling.classList.toggle('hidden')"
                            class="rounded p-1 text-stone-400 hover:text-stone-700"
                            aria-label="More options"
                        >
                            <!-- svg same as above -->
                        </button>
                        <div class="absolute left-0 top-8 z-10 hidden w-44 rounded-md border border-stone-200 bg-white shadow-md">
                            <button
                                type="button"
                                onclick="navigator.clipboard.writeText('{{ route('writers.show', $writer) }}').then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy link', 2000); })"
                                class="w-full px-4 py-2 text-left text-sm text-stone-700 hover:bg-stone-50"
                            >
                                Copy link
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tab bar --}}
                @php
                    $tabs = ['home' => 'Home', 'activity' => 'Activity', 'lists' => 'Lists', 'about' => 'About'];
                @endphp
                <div class="mb-8 flex gap-6 border-b border-stone-200">
                    @foreach ($tabs as $key => $label)
                        <a
                            href="{{ route('writers.show', [$writer, 'tab' => $key]) }}"
                            class="pb-3 text-sm font-medium transition-colors
                                {{ $tab === $key
                                    ? 'border-b-2 border-stone-900 text-stone-900'
                                    : 'text-stone-500 hover:text-stone-800' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Tab content --}}
                @if ($tab === 'home')
                    <div class="space-y-8">
                        @forelse ($items as $post)
                            <article class="flex gap-6 border-b border-stone-200 pb-8">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-stone-500">
                                        <img src="{{ $writer->avatarUrl(24) }}" class="h-6 w-6 rounded-full" alt="">
                                        <span>{{ $writer->name }}</span>
                                        <span>·</span>
                                        <span>{{ $post->published_at?->format('M j, Y') }}</span>
                                    </div>
                                    <h2 class="mb-1 font-serif text-xl font-semibold leading-snug">
                                        <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">
                                            {{ $post->title }}
                                        </a>
                                    </h2>
                                    <p class="text-sm leading-6 text-stone-600">{{ $post->excerpt }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-stone-500">No stories yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'activity')
                    <div class="space-y-6">
                        @forelse ($items as $item)
                            <div class="border-b border-stone-200 pb-6">
                                <p class="mb-1 text-xs text-stone-400">
                                    {{ $item['type'] === 'reaction' ? 'Reacted' : 'Commented' }}
                                    · {{ $item['date']->format('M j, Y') }}
                                </p>
                                <a href="{{ route('posts.show', ['post' => $item['post_slug']]) }}" class="font-serif text-base font-semibold hover:text-accent">
                                    {{ $item['post_title'] }}
                                </a>
                                <p class="mt-1 text-sm text-stone-600">{{ $item['excerpt'] }}</p>
                            </div>
                        @empty
                            <p class="text-stone-500">No activity yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'lists')
                    <div class="space-y-6">
                        @forelse ($items as $circle)
                            <div class="rounded-md border border-stone-200 p-4">
                                <p class="font-semibold text-stone-900">{{ $circle->name }}</p>
                                @if ($circle->post)
                                    <p class="mt-1 text-sm text-stone-500">
                                        Based on:
                                        <a href="{{ route('posts.show', $circle->post) }}" class="hover:text-accent">
                                            {{ $circle->post->title }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        @empty
                            <p class="text-stone-500">No reading circles yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'about')
                    <div class="prose prose-stone max-w-none">
                        @if ($writer->bio)
                            <p>{{ $writer->bio }}</p>
                        @else
                            <p class="text-stone-400">No bio yet.</p>
                        @endif
                        <p class="text-sm text-stone-500">Member since {{ $writer->created_at->format('F Y') }}</p>
                    </div>
                @endif
            </div>

            {{-- Right sidebar --}}
            <aside class="w-64 shrink-0">
                {{-- Profile card --}}
                <div class="mb-6">
                    <img
                        src="{{ $writer->avatarUrl(80) }}"
                        alt="{{ $writer->name }}"
                        class="mb-3 h-20 w-20 rounded-full"
                    >
                    <p class="font-semibold text-stone-900">{{ $writer->name }}</p>
                    @if ($writer->bio)
                        <p class="mt-1 text-sm text-stone-500">{{ $writer->bio }}</p>
                    @endif

                    @auth
                        @if (auth()->id() === $writer->id)
                            <a href="#" class="mt-2 inline-block text-sm text-green-600 hover:underline">Edit profile</a>
                        @else
                            <div class="mt-3 flex gap-2">
                                @if ($isFollowing)
                                    <form method="POST" action="{{ route('writers.unfollow', $writer) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-stone-200 px-3 py-1.5 text-sm text-stone-500 hover:border-stone-400">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('writers.follow', $writer) }}">
                                        @csrf
                                        <button class="rounded-md bg-stone-900 px-3 py-1.5 text-sm text-white hover:bg-stone-700">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('writers.tip', $writer) }}"
                                   class="rounded-md border border-stone-200 px-3 py-1.5 text-sm text-stone-500 hover:border-accent hover:text-accent">
                                    Tip writer
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>

                {{-- Following list --}}
                <div>
                    <p class="mb-3 font-semibold text-stone-900">Following</p>
                    @forelse ($following as $followedUser)
                        <a
                            href="{{ route('writers.show', $followedUser) }}"
                            class="mb-3 flex items-center gap-2 hover:opacity-80"
                        >
                            <img
                                src="{{ $followedUser->avatarUrl(32) }}"
                                alt="{{ $followedUser->name }}"
                                class="h-8 w-8 rounded-full"
                            >
                            <span class="text-sm text-stone-700">{{ $followedUser->name }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-stone-400">Not following anyone yet.</p>
                    @endforelse

                    @if ($followingCount > 5)
                        <a href="#" class="mt-2 text-sm text-stone-500 hover:text-stone-800">
                            See all ({{ $followingCount }})
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-authenticated-layout>
