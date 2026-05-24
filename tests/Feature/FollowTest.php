<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $writer = User::factory()->create();

        $this->actingAs($follower)->post(route('writers.follow', $writer));

        $this->assertTrue($follower->fresh()->isFollowing($writer));
    }

    public function test_user_can_unfollow(): void
    {
        $follower = User::factory()->create();
        $writer = User::factory()->create();
        $follower->following()->attach($writer->id);

        $this->actingAs($follower)->delete(route('writers.unfollow', $writer));

        $this->assertFalse($follower->fresh()->isFollowing($writer));
    }

    public function test_cannot_follow_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('writers.follow', $user))
            ->assertForbidden();
    }
}
