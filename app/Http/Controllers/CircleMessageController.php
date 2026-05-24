<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCircleMessageRequest;
use App\Models\ReadingCircle;
use Illuminate\Http\RedirectResponse;

class CircleMessageController extends Controller
{
    public function store(StoreCircleMessageRequest $request, ReadingCircle $circle): RedirectResponse
    {
        $circle->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return back();
    }
}
