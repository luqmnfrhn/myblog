<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->get();

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->published_at && $post->published_at->isPast(), 404);

        return view('posts.show', compact('post'));
    }
}