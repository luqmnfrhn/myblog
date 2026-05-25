<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticatedLayoutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_sees_sidebar_nav_on_writer_posts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('writer.posts.index'));

        $response->assertOk();
        $response->assertSee('Home');
        $response->assertSee('Library');
        $response->assertSee('Stories');
        $response->assertSee('Stats');
    }

    #[Test]
    public function authenticated_user_can_access_library_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('library.index'))->assertOk()->assertSee('Library');
    }

    #[Test]
    public function authenticated_user_can_access_stats_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('stats.index'))->assertOk()->assertSee('Stats');
    }

    #[Test]
    public function search_route_renders_with_query(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('search.index', ['q' => 'hello']))
            ->assertOk()
            ->assertSee('hello');
    }

    #[Test]
    public function guest_cannot_access_library(): void
    {
        $this->get(route('library.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_access_stats(): void
    {
        $this->get(route('stats.index'))->assertRedirect(route('login'));
    }

    #[Test]
    public function guest_cannot_access_search(): void
    {
        $this->get(route('search.index'))->assertRedirect(route('login'));
    }
}
