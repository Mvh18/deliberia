<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_registration_page_does_not_exist()
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_posting_to_the_registration_endpoint_creates_no_user()
    {
        $this->post('/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'a-very-long-password-123',
            'password_confirmation' => 'a-very-long-password-123',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_no_registration_route_is_registered()
    {
        $names = collect(app('router')->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter();

        $this->assertFalse($names->contains(fn (string $name) => str_starts_with($name, 'register')));
    }

    public function test_the_login_page_has_no_registration_link()
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('/register', false);
    }

    public function test_an_unknown_email_gets_the_same_error_as_a_wrong_password()
    {
        User::factory()->create(['email' => 'real@example.com']);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
        $unknownMessage = session('errors')->first('email');

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'real@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
        $wrongMessage = session('errors')->first('email');

        $this->assertSame($unknownMessage, $wrongMessage);
        $this->assertGuest();
    }

    public function test_guests_cannot_reach_protected_routes()
    {
        foreach (['/dashboard', '/settings/profile', '/settings/security', '/settings/appearance'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_the_health_endpoint_is_public()
    {
        $this->get('/up')->assertOk();
    }
}
