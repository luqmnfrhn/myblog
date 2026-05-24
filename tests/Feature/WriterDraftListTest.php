<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterDraftListTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_can_view_their_own_posts(): void
    {
        $user = User::factory()->create();
        $draft = Post::factory()->for($user)->create(['published_at' => null]);
        $published = Post::factory()->for($user)->published()->create();
        $other = Post::factory()->published()->create();

        $response = $this->actingAs($user)->get(route('writer.posts.index'));

        $response->assertOk();
        $response->assertSee($draft->title);
        $response->assertSee($published->title);
        $response->assertDontSee($other->title);
    }

    public function test_guest_cannot_view_writer_post_list(): void
    {
        $this->get(route('writer.posts.index'))
            ->assertRedirect(route('login'));
    }
}
