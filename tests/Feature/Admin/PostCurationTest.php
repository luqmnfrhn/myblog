<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_feature_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)->patch(route('admin.posts.feature', $post));

        $this->assertTrue($post->fresh()->is_featured);
    }

    public function test_regular_user_cannot_feature_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)
            ->patch(route('admin.posts.feature', $post))
            ->assertForbidden();
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)
            ->delete(route('writer.posts.destroy', $post))
            ->assertRedirect();

        $this->assertModelMissing($post);
    }

    public function test_regular_user_cannot_delete_others_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for(User::factory()->create())->published()->create();

        $this->actingAs($user)
            ->delete(route('writer.posts.destroy', $post))
            ->assertForbidden();
    }

    public function test_featured_posts_appear_first_on_homepage(): void
    {
        $regular = Post::factory()->published()->create(['title' => 'Regular story']);
        $featured = Post::factory()->published()->create([
            'title' => 'Featured story',
            'is_featured' => true,
        ]);

        $content = $this->get(route('posts.index'))->getContent();

        $this->assertLessThan(
            strpos($content, $regular->title),
            strpos($content, $featured->title)
        );
    }
}
