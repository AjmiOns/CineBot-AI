<?php

namespace Database\Factories;

use App\Models\MovieInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieInteraction>
 */
class MovieInteractionFactory extends Factory
{
    protected $model = MovieInteraction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'tmdb_id'      => fake()->unique()->numberBetween(1, 999999),
            'movie_title'  => fake()->sentence(3),
            'liked'        => null,
            'rating'       => null,
            'watched_at'   => null,
            'search_count' => 0,
        ];
    }
}
