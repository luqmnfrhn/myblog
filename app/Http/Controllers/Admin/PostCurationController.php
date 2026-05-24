<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class PostCurationController extends Controller
{
    public function feature(Post $post): RedirectResponse
    {
        $post->update(['is_featured' => ! $post->is_featured]);

        return back();
    }
}
