<x-authenticated-layout>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="font-serif text-3xl font-semibold">My stories</h1>
            <a href="{{ route('writer.posts.create') }}" class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">New story</a>
        </div>

        @if ($posts->isEmpty())
            <p class="text-stone-500">No stories yet. <a href="{{ route('writer.posts.create') }}" class="underline">Write one.</a></p>
        @else
            <div class="divide-y divide-stone-200">
                @foreach ($posts as $post)
                    <div class="flex items-center justify-between py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-stone-900">{{ $post->title }}</p>
                            <div class="mt-1 flex items-center gap-3 text-sm text-stone-500">
                                @if ($post->hidden_at)
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Hidden</span>
                                @elseif ($post->published_at)
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Published</span>
                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                @else
                                    <span class="rounded bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-600">Draft</span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex shrink-0 gap-3">
                            <a href="{{ route('writer.posts.edit', $post) }}" class="text-sm text-stone-600 hover:text-stone-900">Edit</a>
                            <form method="POST" action="{{ route('writer.posts.destroy', $post) }}" onsubmit="return confirm('Delete this story?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-stone-400 hover:text-red-600">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-authenticated-layout>
