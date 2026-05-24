<x-app-layout>
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-8 font-serif text-3xl font-semibold">Edit story</h1>

        <form id="update-post" method="POST" action="{{ route('writer.posts.update', $post) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <input name="title" type="text" value="{{ old('title', $post->title) }}" class="w-full border-0 border-b border-stone-200 bg-transparent pb-2 font-serif text-3xl focus:border-accent focus:ring-0">
            <x-input-error :messages="$errors->get('title')" class="mt-2" />

            <input name="excerpt" type="text" value="{{ old('excerpt', $post->excerpt) }}" class="w-full border-0 border-b border-stone-200 bg-transparent pb-2 text-stone-600 focus:border-accent focus:ring-0">
            <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />

            <textarea name="body" rows="18" class="w-full resize-none border-0 bg-transparent font-serif text-lg leading-8 focus:ring-0">{{ old('body', $post->body) }}</textarea>
            <x-input-error :messages="$errors->get('body')" class="mt-2" />
        </form>

        <div class="mt-6 flex flex-wrap gap-4 border-t border-stone-200 pt-4">
            <button form="update-post" type="submit" class="rounded-md bg-stone-900 px-5 py-2 text-sm font-medium text-white hover:bg-stone-700">Save</button>

            @if (! $post->published_at)
                <form method="POST" action="{{ route('writer.posts.publish', $post) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-md bg-accent px-5 py-2 text-sm font-medium text-white hover:bg-accent-light">Publish</button>
                </form>
            @endif

            <form method="POST" action="{{ route('writer.posts.destroy', $post) }}" onsubmit="return confirm('Delete this story?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md border border-stone-200 px-5 py-2 text-sm text-stone-500 hover:border-red-300 hover:text-red-600">Delete</button>
            </form>
        </div>
    </div>
</x-app-layout>
