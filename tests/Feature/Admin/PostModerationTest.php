<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_regular_user_cannot_view_admin_post_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.posts.index'))
            ->assertForbidden();
    }

    public function test_admin_can_delete_any_post_from_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)
            ->delete(route('admin.posts.destroy', $post))
            ->assertRedirect();

        $this->assertModelMissing($post);
    }
}
