<?php

namespace Tests\Feature;

use App\Models\MovieInteraction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MovieFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Aucun appel réseau réel vers TMDB pendant les tests — l'enrichissement
        // du profil (genres/acteurs/réalisateur) est testé séparément et ne
        // doit pas rendre cette suite dépendante d'une API externe.
        Http::fake();
    }

    public function test_authenticated_user_can_like_a_movie(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'like',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('movie_interactions', [
            'user_id'     => $user->id,
            'tmdb_id'     => 550,
            'movie_title' => 'Fight Club',
            'liked'       => true,
        ]);
    }

    public function test_authenticated_user_can_dislike_a_movie(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'dislike',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('movie_interactions', [
            'user_id' => $user->id,
            'tmdb_id' => 550,
            'liked'   => false,
        ]);
    }

    public function test_authenticated_user_can_rate_a_movie(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'rate',
            'rating'  => 5,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('movie_interactions', [
            'user_id' => $user->id,
            'tmdb_id' => 550,
            'rating'  => 5,
        ]);
    }

    public function test_rating_action_requires_a_rating_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'rate',
            // 'rating' volontairement omis
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('movie_interactions', ['tmdb_id' => 550]);
    }

    public function test_rating_value_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'rate',
            'rating'  => 8,
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_submit_feedback(): void
    {
        $response = $this->postJson('/api/movies/feedback', [
            'tmdb_id' => 550,
            'title'   => 'Fight Club',
            'action'  => 'like',
        ]);

        $response->assertStatus(401);
    }

    public function test_liking_the_same_movie_twice_updates_instead_of_duplicating(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550, 'title' => 'Fight Club', 'action' => 'like',
        ]);
        $this->actingAs($user)->postJson('/api/movies/feedback', [
            'tmdb_id' => 550, 'title' => 'Fight Club', 'action' => 'dislike',
        ]);

        $this->assertSame(1, MovieInteraction::where('user_id', $user->id)->where('tmdb_id', 550)->count());
        $this->assertDatabaseHas('movie_interactions', [
            'user_id' => $user->id,
            'tmdb_id' => 550,
            'liked'   => false,
        ]);
    }

    public function test_user_can_view_their_liked_movies_as_favorites(): void
    {
        $user = User::factory()->create();

        MovieInteraction::factory()->create(['user_id' => $user->id, 'liked' => true,  'movie_title' => 'Interstellar']);
        MovieInteraction::factory()->create(['user_id' => $user->id, 'liked' => false, 'movie_title' => 'Cats']);

        $response = $this->actingAs($user)->getJson('/api/user/favorites');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['movie_title' => 'Interstellar']);
    }
}
