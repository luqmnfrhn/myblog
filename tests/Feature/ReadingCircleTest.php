<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\ReadingCircle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingCircleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_reading_circle_for_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.circles.store', $post), [
            'name' => 'Our Book Club',
        ]);

        $this->assertDatabaseHas('reading_circles', [
            'post_id' => $post->id,
            'name' => 'Our Book Club',
        ]);
    }

    public function test_creator_is_auto_joined_to_circle(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.circles.store', $post), [
            'name' => 'My Circle',
        ]);

        $circle = ReadingCircle::query()->firstOrFail();

        $this->assertTrue($circle->members->contains($user));
    }

    public function test_member_can_post_message_to_circle(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();
        $circle = ReadingCircle::factory()->for($post)->for($user, 'creator')->create();
        $circle->members()->attach($user->id, ['joined_at' => now()]);

        $this->actingAs($user)->post(route('circles.messages.store', $circle), [
            'body' => 'What did everyone think of the ending?',
        ]);

        $this->assertDatabaseHas('circle_messages', [
            'circle_id' => $circle->id,
            'body' => 'What did everyone think of the ending?',
        ]);
    }

    public function test_non_member_cannot_post_to_circle(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();
        $post = Post::factory()->published()->create();
        $circle = ReadingCircle::factory()->for($post)->for($creator, 'creator')->create();

        $this->actingAs($outsider)
            ->post(route('circles.messages.store', $circle), ['body' => 'Sneaking in!'])
            ->assertForbidden();
    }
}
