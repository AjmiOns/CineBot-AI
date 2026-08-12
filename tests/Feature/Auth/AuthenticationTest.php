<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_chatbot(): void
    {
        $response = $this->get('/chatbot');

        $response->assertRedirect('/login');
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Ons Ajmi',
            'email'                 => 'ons@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('chatbot'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ons@example.com']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ons@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'Ons Ajmi',
            'email'                 => 'ons@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password', // mot de passe par défaut de la UserFactory
        ]);

        $response->assertRedirect(route('chatbot'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_forgot_password_sends_a_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_does_not_reveal_whether_email_exists(): void
    {
        // Même réponse pour un email inexistant — protection contre l'énumération de comptes.
        $response = $this->post('/forgot-password', ['email' => 'inconnu@example.com']);

        $response->assertSessionDoesntHaveErrors('email');
    }

    public function test_user_can_delete_their_account_with_correct_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/profile', ['password' => 'password']);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_account_deletion_requires_correct_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete('/profile', ['password' => 'wrong-password']);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
