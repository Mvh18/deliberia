<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command
{
    /**
     * The registration page is disabled, so users are created from the CLI.
     * The password is never accepted as a literal argument (it would end up
     * in the shell history): it is prompted for, or read from an environment
     * variable whose *name* is given in --password-env.
     *
     * @var string
     */
    protected $signature = 'deliberia:user:create
        {--name= : Display name}
        {--email= : Email address (must be unique)}
        {--password-env= : Name of the environment variable that holds the password}';

    /**
     * @var string
     */
    protected $description = 'Create a Deliberia user (registration is disabled on the web)';

    public function handle(): int
    {
        $name = $this->stringOption('name');
        $email = $this->stringOption('email');
        $passwordEnv = $this->stringOption('password-env');

        if ($this->input->isInteractive()) {
            $name ??= text(label: 'Name', required: true);
            $email ??= text(label: 'Email', required: true);
        }

        $password = $passwordEnv !== null
            ? $this->passwordFromEnvironment($passwordEnv)
            : $this->promptForPassword();

        if ($password === null) {
            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::default()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        // Created by an administrator from the CLI, so the email counts as verified.
        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ])->save();

        $this->components->info("User {$user->email} created.");

        return self::SUCCESS;
    }

    protected function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    protected function passwordFromEnvironment(string $variable): ?string
    {
        $value = $_ENV[$variable] ?? $_SERVER[$variable] ?? getenv($variable);

        if (! is_string($value) || $value === '') {
            $this->components->error("The environment variable {$variable} is not set or is empty.");

            return null;
        }

        return $value;
    }

    protected function promptForPassword(): ?string
    {
        if (! $this->input->isInteractive()) {
            $this->components->error('Provide --password-env=VAR when running non-interactively.');

            return null;
        }

        $password = password(label: 'Password', required: true);
        $confirmation = password(label: 'Confirm password', required: true);

        if ($password !== $confirmation) {
            $this->components->error('The password confirmation does not match.');

            return null;
        }

        return $password;
    }
}
