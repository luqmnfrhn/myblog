<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WriterProfileController extends Controller
{
    public function show(Request $request, User $writer): View
    {
        $tab = $request->query('tab', 'home');

        $isFollowing = Auth::check()
            && ! Auth::user()->is($writer)
            && Auth::user()->isFollowing($writer);

        $following = $writer->following()->latest('follows.created_at')->take(5)->get();
        $followingCount = $writer->following()->count();

        $items = match ($tab) {
            'activity' => $this->activityItems($writer),
            'lists' => $writer->readingCircles()->with('post')->latest()->get(),
            'about' => new Collection,
            default => $writer->posts()->published()->latest('published_at')->get(),
        };

        return view('writers.show', compact('writer', 'tab', 'following', 'isFollowing', 'followingCount', 'items'));
    }

    /**
     * @return Collection<int, array{type: string, post_title: string, post_slug: string, excerpt: string, date: Carbon}>
     */
    private function activityItems(User $writer): Collection
    {
        $reactions = $writer->reactions()
            ->with('post')
            ->latest()
            ->get()
            ->map(fn ($reaction) => [
                'type' => 'reaction',
                'post_title' => $reaction->post->title,
                'post_slug' => $reaction->post->slug,
                'excerpt' => $reaction->type->label(),
                'date' => $reaction->created_at,
            ]);

        $comments = $writer->comments()
            ->with('post')
            ->latest()
            ->get()
            ->map(fn ($comment) => [
                'type' => 'comment',
                'post_title' => $comment->post->title,
                'post_slug' => $comment->post->slug,
                'excerpt' => Str::limit($comment->body, 80),
                'date' => $comment->created_at,
            ]);

        return $reactions->concat($comments)->sortByDesc('date')->values();
    }
}
