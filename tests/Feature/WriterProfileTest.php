<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_profile_shows_published_posts(): void
    {
        $writer = User::factory()->create();
        $published = Post::factory()->for($writer)->published()->create();
        $draft = Post::factory()->for($writer)->create(['published_at' => null]);

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
    }

    public function test_writer_profile_shows_bio(): void
    {
        $writer = User::factory()->create(['bio' => 'Sharing what inspires me.']);

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee('Sharing what inspires me.');
    }
}
