<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactionRequest;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\RedirectResponse;

class ReactionController extends Controller
{
    public function store(StoreReactionRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();

        $existingReaction = Reaction::query()
            ->whereBelongsTo($post)
            ->whereBelongsTo($request->user())
            ->where('type', $validated['type'])
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();

            return back();
        }

        $post->reactions()->create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'created_at' => now(),
        ]);

        return back();
    }
}
