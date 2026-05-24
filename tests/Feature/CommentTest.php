<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'Great read!',
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'Great read!',
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_reply_to_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();
        $parent = Comment::factory()->for($post)->for($user)->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'I agree!',
            'parent_id' => $parent->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'body' => 'I agree!',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_guest_cannot_comment(): void
    {
        $post = Post::factory()->published()->create();

        $this->post(route('posts.comments.store', $post), ['body' => 'Hi'])
            ->assertRedirect(route('login'));
    }
}
