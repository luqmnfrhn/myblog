@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl">
        <h1 class="mb-8 text-2xl font-bold text-white">All posts</h1>

        <div class="overflow-hidden rounded-lg border border-stone-700">
            <table class="w-full text-left text-sm text-stone-300">
                <thead class="bg-stone-800 text-xs uppercase text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Author</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-700 bg-stone-900">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="px-4 py-3 font-medium text-white">{{ $post->title }}</td>
                            <td class="px-4 py-3">{{ $post->author->name }}</td>
                            <td class="px-4 py-3">
                                @if ($post->hidden_at)
                                    <span class="rounded bg-red-900 px-2 py-0.5 text-xs font-medium text-red-300">Hidden</span>
                                @elseif ($post->published_at)
                                    <span class="rounded bg-green-900 px-2 py-0.5 text-xs font-medium text-green-300">Published</span>
                                @else
                                    <span class="rounded bg-stone-700 px-2 py-0.5 text-xs font-medium text-stone-300">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('admin.posts.visibility', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs text-stone-400 hover:text-white">
                                            {{ $post->hidden_at ? 'Unhide' : 'Hide' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-stone-500">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
