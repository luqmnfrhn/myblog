<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReadingCircleRequest;
use App\Models\Post;
use App\Models\ReadingCircle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingCircleController extends Controller
{
    public function store(StoreReadingCircleRequest $request, Post $post): RedirectResponse
    {
        $circle = $post->circles()->create([
            'creator_id' => $request->user()->id,
            'name' => $request->validated('name'),
        ]);

        $circle->members()->attach($request->user()->id, ['joined_at' => now()]);

        return redirect()->route('circles.show', $circle);
    }

    public function show(ReadingCircle $circle): View
    {
        abort_unless($circle->hasMember(auth()->user()), 403);

        $circle->load(['post', 'members', 'messages']);

        return view('circles.show', compact('circle'));
    }

    public function join(Request $request, ReadingCircle $circle): RedirectResponse
    {
        if (! $circle->hasMember($request->user())) {
            $circle->members()->attach($request->user()->id, ['joined_at' => now()]);
        }

        return redirect()->route('circles.show', $circle);
    }
}
