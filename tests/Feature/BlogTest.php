<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_published_posts_on_the_home_page(): void
    {
        $post = Post::create([
            'title' => 'First Post',
            'slug' => 'first-post',
            'excerpt' => 'A short introduction.',
            'body' => 'Post body content.',
            'published_at' => now(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee($post->excerpt);
    }

    public function test_it_shows_a_post_detail_page(): void
    {
        $post = Post::create([
            'title' => 'Second Post',
            'slug' => 'second-post',
            'excerpt' => 'Another short introduction.',
            'body' => "Line one.\n\nLine two.",
            'published_at' => now(),
        ]);

        $response = $this->get('/posts/second-post');

        $response->assertOk();
        $response->assertSee($post->title);
        $response->assertSee($post->excerpt);
        $response->assertSee('Line one.');
        $response->assertSee('Line two.');
    }
}