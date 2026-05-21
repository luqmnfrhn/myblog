<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Post::query()->insert([
            [
                'title' => 'Welcome to the blog',
                'slug' => 'welcome-to-the-blog',
                'excerpt' => 'This is the first post on your new Laravel blog.',
                'body' => "This simple blog is ready for your own content.\n\nYou can now add categories, tags, images, and an admin area later.",
                'published_at' => now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Building posts in Laravel',
                'slug' => 'building-posts-in-laravel',
                'excerpt' => 'A short example of how blog content will look on the site.',
                'body' => "Each post is stored in the database and shown on the homepage.\n\nThe slug keeps the URLs clean and easy to share.",
                'published_at' => now()->subDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Next steps for your blog',
                'slug' => 'next-steps-for-your-blog',
                'excerpt' => 'Ideas for expanding this starter into a full blog platform.',
                'body' => "You can add an admin dashboard, markdown editor, categories, and comments when you're ready.\n\nFor now, the site is intentionally small and easy to understand.",
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
