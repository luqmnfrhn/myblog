<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TipTest extends TestCase
{
    use RefreshDatabase;

    public function test_tip_page_requires_auth(): void
    {
        $writer = User::factory()->create();

        $this->get(route('writers.tip', $writer))
            ->assertRedirect(route('login'));
    }

    public function test_user_cannot_tip_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('writers.tip', $user))
            ->assertForbidden();
    }

    public function test_tip_page_shows_for_authenticated_user(): void
    {
        $tipper = User::factory()->create();
        $writer = User::factory()->create();

        $this->actingAs($tipper)
            ->get(route('writers.tip', $writer))
            ->assertOk();
    }
}
