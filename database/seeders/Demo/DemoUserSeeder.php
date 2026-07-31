<?php

namespace Database\Seeders\Demo;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * `PO-U17.0-001` §5/§6 — the canonical demo identity. Idempotent: looks up
 * by email first, so re-running never creates a second demo user. Password
 * comes from `SLIPGUARD_DEMO_PASSWORD` when set; otherwise a random one is
 * generated and returned to the caller to print once — never logged,
 * never committed, never hardcoded.
 */
class DemoUserSeeder
{
    public const EMAIL = 'demo@slipguard.local';

    public const NAME = 'Tunde Adeyemi';

    /**
     * @return array{user: User, generated_password: ?string}
     */
    public function run(): array
    {
        $configuredPassword = env('SLIPGUARD_DEMO_PASSWORD');
        $generatedPassword = $configuredPassword ? null : Str::password(16);
        $password = $configuredPassword ?: $generatedPassword;

        $user = User::firstOrNew(['email' => self::EMAIL]);
        $user->name = self::NAME;
        $user->is_demo = true;
        $user->email_verified_at = $user->email_verified_at ?? now();

        // Only overwrite the password on first creation, or when the operator
        // explicitly configured one via the env var — re-running the command
        // must not invalidate a password already shared for a live demo.
        if (! $user->exists || $configuredPassword) {
            $user->password = Hash::make($password);
        } else {
            $generatedPassword = null;
        }

        $user->save();

        return ['user' => $user, 'generated_password' => $generatedPassword];
    }
}
