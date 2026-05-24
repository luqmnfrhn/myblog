<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WriterProfileController extends Controller
{
    public function show(User $writer): View
    {
        $posts = $writer->posts()
            ->published()
            ->latest('published_at')
            ->get();

        $isFollowing = Auth::check()
            && ! Auth::user()->is($writer)
            && Auth::user()->isFollowing($writer);

        return view('writers.show', compact('writer', 'posts', 'isFollowing'));
    }
}
