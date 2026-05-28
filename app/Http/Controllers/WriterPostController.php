<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWriterPostRequest;
use App\Http\Requests\UpdateWriterPostRequest;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WriterPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = $request->user()->posts()->latest()->get();

        return view('writer.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('writer.posts.create');
    }

    public function store(StoreWriterPostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $post = $request->user()->posts()->create([
            ...$validated,
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
        ]);

        return redirect()->route('writer.posts.edit', $post);
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('writer.posts.edit', compact('post'));
    }

    public function update(UpdateWriterPostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function publish(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post->update(['published_at' => now()]);

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}
