<x-authenticated-layout>
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-8 font-serif text-3xl font-semibold">New story</h1>

        <form method="POST" action="{{ route('writer.posts.store') }}" class="space-y-6">
            @csrf

            <div>
                <input name="title" type="text" placeholder="Title" class="w-full border-0 border-b border-stone-300 bg-transparent pb-2 font-serif text-3xl text-stone-950 placeholder:text-stone-500 caret-stone-950 focus:border-accent focus:ring-0" value="{{ old('title') }}">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <input name="excerpt" type="text" placeholder="Write a short excerpt..." class="w-full border-0 border-b border-stone-300 bg-transparent pb-2 text-stone-900 placeholder:text-stone-500 caret-stone-950 focus:border-accent focus:ring-0" value="{{ old('excerpt') }}">
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <textarea name="body" rows="18" placeholder="Tell your story..." class="w-full resize-none border-0 bg-transparent font-serif text-lg leading-8 text-stone-950 placeholder:text-stone-500 caret-stone-950 focus:ring-0">{{ old('body') }}</textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div class="flex gap-4 border-t border-stone-200 pt-4">
                <button type="submit" class="rounded-md bg-stone-900 px-5 py-2 text-sm font-medium text-white hover:bg-stone-700">Save draft</button>
                <a href="{{ route('posts.index') }}" class="self-center text-sm text-stone-600 hover:text-stone-900">Cancel</a>
            </div>
        </form>
    </div>
</x-authenticated-layout>
