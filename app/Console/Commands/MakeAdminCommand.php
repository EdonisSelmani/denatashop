<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MakeAdminCommand extends Command
{
    protected $signature = 'admin:make
        {email : Admin email address}
        {--name= : Name for a new admin user}
        {--password= : Password for a new admin user}';

    protected $description = 'Create a new admin user or promote an existing user to admin.';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("Existing user {$email} is now an admin.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: $this->ask('Admin name');
        $password = $this->option('password') ?: $this->secret('Admin password');

        validator([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ])->validate();

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Admin user {$email} was created.");

        return self::SUCCESS;
    }
}
