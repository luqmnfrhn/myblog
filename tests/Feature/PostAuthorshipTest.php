<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostAuthorshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertTrue($user->is($post->author));
    }

    public function test_user_has_many_posts(): void
    {
        $user = User::factory()->create();

        Post::factory()->count(3)->for($user)->create();

        $this->assertCount(3, $user->posts);
    }
}
