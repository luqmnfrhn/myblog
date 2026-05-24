<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PostVisibilityController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        Gate::authorize('hide', $post);

        $post->update([
            'hidden_at' => $post->hidden_at ? null : now(),
        ]);

        return back();
    }
}
