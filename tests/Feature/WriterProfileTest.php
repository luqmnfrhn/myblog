<?php

namespace Tests\Feature;

use App\Enums\ReactionType;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\ReadingCircle;
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

    public function test_activity_tab_shows_writer_reactions(): void
    {
        $writer = User::factory()->create();
        $post = Post::factory()->published()->create();
        Reaction::factory()->create([
            'user_id' => $writer->id,
            'post_id' => $post->id,
            'type' => ReactionType::ThoughtProvoking->value,
        ]);

        $response = $this->get(route('writers.show', [$writer, 'tab' => 'activity']));
        $response->assertOk();
        $response->assertSee($post->title);
    }

    public function test_activity_tab_shows_writer_comments(): void
    {
        $writer = User::factory()->create();
        $post = Post::factory()->published()->create();
        Comment::factory()->create([
            'user_id' => $writer->id,
            'post_id' => $post->id,
            'body' => 'A thoughtful comment.',
        ]);

        $response = $this->get(route('writers.show', [$writer, 'tab' => 'activity']));

        $response->assertOk();
        $response->assertSee('A thoughtful comment.');
    }

    public function test_lists_tab_shows_reading_circles(): void
    {
        $writer = User::factory()->create();
        $circle = ReadingCircle::factory()->create(['creator_id' => $writer->id]);

        $response = $this->get(route('writers.show', [$writer, 'tab' => 'lists']));

        $response->assertOk();
        $response->assertSee($circle->name);
    }

    public function test_about_tab_shows_bio_and_join_date(): void
    {
        $writer = User::factory()->create(['bio' => 'My bio text.']);

        $response = $this->get(route('writers.show', [$writer, 'tab' => 'about']));

        $response->assertOk();
        $response->assertSee('My bio text.');
        $response->assertSee($writer->created_at->format('F Y'));
    }

    public function test_profile_shows_gravatar_avatar(): void
    {
        $writer = User::factory()->create();
        $hash = md5(strtolower(trim($writer->email)));

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee($hash);
    }

    public function test_profile_shows_following_list_in_sidebar(): void
    {
        $writer = User::factory()->create();
        $followed = User::factory()->create(['name' => 'Jane Followed']);
        $writer->following()->attach($followed);

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee('Jane Followed');
    }

    public function test_profile_shows_edit_link_to_own_profile(): void
    {
        $writer = User::factory()->create();

        $response = $this->actingAs($writer)->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee('Edit profile');
    }

    public function test_profile_hides_edit_link_from_other_users(): void
    {
        $writer = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertDontSee('Edit profile');
    }

    public function test_tab_bar_renders_all_four_tabs(): void
    {
        $writer = User::factory()->create();

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee('Home');
        $response->assertSee('Activity');
        $response->assertSee('Lists');
        $response->assertSee('About');
    }
}
