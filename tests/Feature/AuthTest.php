<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_factory_state_sets_is_admin_true(): void
    {
        $user = User::factory()->admin()->create();
        $this->assertTrue($user->is_admin);
    }

    public function test_admin_can_login_via_admin_login_form(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_login_via_admin_login_form(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_new_user_can_authenticate_via_google(): void
    {
        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->method('getId')->willReturn('google-123');
        $socialiteUser->method('getEmail')->willReturn('user@example.com');
        $socialiteUser->method('getName')->willReturn('Test User');
        $socialiteUser->token = 'fake-token';

        $provider = $this->createMock(GoogleProvider::class);
        $provider->method('user')->willReturn($socialiteUser);

        $this->mock(Factory::class)
            ->shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
        $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_id' => 'google-123']);
    }

    public function test_existing_social_account_logs_in_linked_user(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $user->socialAccounts()->create([
            'provider' => 'github',
            'provider_id' => 'gh-456',
            'token' => 'old-token',
        ]);

        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->method('getId')->willReturn('gh-456');
        $socialiteUser->method('getEmail')->willReturn('existing@example.com');
        $socialiteUser->method('getName')->willReturn($user->name);
        $socialiteUser->token = 'new-token';

        $provider = $this->createMock(GithubProvider::class);
        $provider->method('user')->willReturn($socialiteUser);

        $this->mock(Factory::class)
            ->shouldReceive('driver')
            ->with('github')
            ->andReturn($provider);

        $this->get('/auth/github/callback');

        $this->assertAuthenticatedAs($user);
    }
}
