<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\ReadingCircle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingCircle>
 */
class ReadingCircleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'creator_id' => User::factory(),
            'name' => fake()->words(3, true),
        ];
    }
}
