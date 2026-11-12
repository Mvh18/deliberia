<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG = 'correct-horse-battery-staple';

    public function test_it_creates_a_user_interactively()
    {
        $this->artisan('deliberia:user:create')
            ->expectsQuestion('Name', 'Ada Lovelace')
            ->expectsQuestion('Email', 'ada@example.com')
            ->expectsQuestion('Password', self::STRONG)
            ->expectsQuestion('Confirm password', self::STRONG)
            ->assertExitCode(0);

        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $this->assertSame('Ada Lovelace', $user->name);
        $this->assertTrue(Hash::check(self::STRONG, $user->password));
        $this->assertNotSame(self::STRONG, $user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_creates_a_user_from_an_environment_variable()
    {
        putenv('DELIBERIA_TEST_PASSWORD='.self::STRONG);

        try {
            $this->artisan('deliberia:user:create', [
                '--name' => 'Grace Hopper',
                '--email' => 'grace@example.com',
                '--password-env' => 'DELIBERIA_TEST_PASSWORD',
            ])->assertExitCode(0);
        } finally {
            putenv('DELIBERIA_TEST_PASSWORD');
        }

        $user = User::where('email', 'grace@example.com')->firstOrFail();
        $this->assertTrue(Hash::check(self::STRONG, $user->password));
    }

    public function test_it_fails_when_the_email_already_exists()
    {
        $existing = User::factory()->create(['email' => 'taken@example.com', 'name' => 'Original']);
        $originalHash = $existing->password;
        putenv('DELIBERIA_TEST_PASSWORD='.self::STRONG);

        try {
            $this->artisan('deliberia:user:create', [
                '--name' => 'Other',
                '--email' => 'taken@example.com',
                '--password-env' => 'DELIBERIA_TEST_PASSWORD',
            ])->assertExitCode(1);
        } finally {
            putenv('DELIBERIA_TEST_PASSWORD');
        }

        $this->assertDatabaseCount('users', 1);
        $existing->refresh();
        $this->assertSame('Original', $existing->name);
        $this->assertSame($originalHash, $existing->password);
    }

    public function test_it_rejects_a_weak_password()
    {
        putenv('DELIBERIA_TEST_PASSWORD=short');

        try {
            $this->artisan('deliberia:user:create', [
                '--name' => 'Weak',
                '--email' => 'weak@example.com',
                '--password-env' => 'DELIBERIA_TEST_PASSWORD',
            ])->assertExitCode(1);
        } finally {
            putenv('DELIBERIA_TEST_PASSWORD');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_fails_when_the_password_variable_is_not_set()
    {
        $this->artisan('deliberia:user:create', [
            '--name' => 'Nobody',
            '--email' => 'nobody@example.com',
            '--password-env' => 'DELIBERIA_UNSET_VARIABLE',
        ])->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_rejects_a_mismatched_confirmation()
    {
        $this->artisan('deliberia:user:create')
            ->expectsQuestion('Name', 'Ada')
            ->expectsQuestion('Email', 'ada@example.com')
            ->expectsQuestion('Password', self::STRONG)
            ->expectsQuestion('Confirm password', 'something-else-entirely')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_does_not_accept_a_literal_password_option()
    {
        $this->expectException(InvalidOptionException::class);

        $this->artisan('deliberia:user:create', [
            '--name' => 'X',
            '--email' => 'x@example.com',
            '--password' => self::STRONG,
        ]);
    }
}
