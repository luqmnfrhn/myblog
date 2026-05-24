<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipRequest;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class TipController extends Controller
{
    public function create(Request $request, User $writer): View
    {
        abort_if($request->user()->is($writer), 403);

        return view('tips.checkout', [
            'writer' => $writer,
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function store(StoreTipRequest $request, User $writer): RedirectResponse
    {
        $validated = $request->validated();

        $stripe = new StripeClient(config('services.stripe.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount' => $validated['amount_cents'],
            'currency' => 'myr',
            'metadata' => [
                'sender_id' => $request->user()->id,
                'receiver_id' => $writer->id,
            ],
        ]);

        Tip::query()->create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $writer->id,
            'amount_cents' => $validated['amount_cents'],
            'stripe_payment_intent_id' => $intent->id,
            'status' => 'pending',
        ]);

        return redirect()->away($intent->next_action?->redirect_to_url?->url ?? route('writers.show', $writer));
    }
}
