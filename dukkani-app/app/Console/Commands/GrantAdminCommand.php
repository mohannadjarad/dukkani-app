<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Grants (or revokes) platform-admin access for an existing user. Used once on
 * production to create the first admin, since there is no self-serve signup.
 */
class GrantAdminCommand extends Command
{
    protected $signature = 'dukkani:admin:grant {email : The user email} {--revoke : Remove admin access instead}';

    protected $description = 'Grant or revoke platform admin access for a user';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->components->error("No user found with email {$email}.");

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => ! $this->option('revoke')])->save();

        $this->components->info($this->option('revoke')
            ? "Admin access revoked for {$user->email}."
            : "Admin access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
