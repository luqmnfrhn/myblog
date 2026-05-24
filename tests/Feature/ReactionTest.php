<?php

namespace Tests\Feature;

use App\Enums\ReactionType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_react_to_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::ThoughtProvoking->value,
        ]);

        $this->assertDatabaseHas('reactions', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'type' => ReactionType::ThoughtProvoking->value,
        ]);
    }

    public function test_user_can_toggle_reaction_off(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::BeautifullyWritten->value,
        ]);

        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::BeautifullyWritten->value,
        ]);

        $this->assertDatabaseMissing('reactions', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
