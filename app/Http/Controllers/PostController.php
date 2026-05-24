<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with('author')
            ->published()
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->get();

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        abort_unless($post->published_at && $post->published_at->isPast(), 404);

        $post->load(['author', 'comments', 'reactions', 'circles.members']);

        return view('posts.show', compact('post'));
    }
}
