<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_home_splash_login_and_register_pages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Loading securely')
            ->assertSee('Control your business');
        $this->get(route('login'))->assertOk()->assertSee('Log in');
        $this->get(route('register'))->assertOk()->assertSee('Create account');
    }

    public function test_user_can_register_and_open_dashboard(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'demo@example.com']);
        $this->get(route('dashboard'))->assertOk()->assertSee('Welcome, Demo User');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'Secret123',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
