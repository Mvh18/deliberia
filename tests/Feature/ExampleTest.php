<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_redirects_to_the_dashboard()
    {
        $this->get(route('home'))->assertRedirect('/dashboard');
    }

    public function test_guests_following_the_root_end_up_at_login()
    {
        $this->followingRedirects()
            ->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/login'));
    }
}
