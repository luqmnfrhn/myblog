<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('writer.posts.store'), [
            'title' => 'My First Post',
            'excerpt' => 'A short excerpt.',
            'body' => 'The full body content here.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post',
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $this->post(route('writer.posts.store'), ['title' => 'Hack'])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_publish_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['published_at' => null]);

        $this->actingAs($user)->patch(route('writer.posts.publish', $post));

        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_user_cannot_edit_others_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->actingAs($other)
            ->patch(route('writer.posts.update', $post), [
                'title' => 'Hijacked',
                'excerpt' => 'Bad idea.',
                'body' => 'Nope.',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('writer.posts.destroy', $post))
            ->assertRedirect();

        $this->assertModelMissing($post);
    }

    public function test_hidden_post_does_not_appear_in_published_scope(): void
    {
        $post = Post::factory()->published()->create(['hidden_at' => now()]);

        $results = Post::query()->published()->get();

        $this->assertNotContains($post->id, $results->pluck('id')->all());
    }
}
