<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('slipguard:make-internal-user {email} {--force : Allow running in production}')]
#[Description('Promote an existing user to internal staff status')]
class SlipguardMakeInternalUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Refusing to run in production without --force.');

            return self::FAILURE;
        }

        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        $user->is_internal = true;
        $user->save();

        $this->info("User [{$email}] has been promoted to internal.");

        return self::SUCCESS;
    }
}
