<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, User $writer): RedirectResponse
    {
        abort_if($request->user()->is($writer), 403);

        $request->user()->following()->syncWithoutDetaching([$writer->id]);

        return back();
    }

    public function unfollow(Request $request, User $writer): RedirectResponse
    {
        $request->user()->following()->detach($writer->id);

        return back();
    }
}
