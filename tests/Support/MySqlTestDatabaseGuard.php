<?php

namespace Tests\Support;

use RuntimeException;

/**
 * `AO-D1` (`PO-RC1-013`) — the positive-evidence rule a resolved database
 * name and application environment must both satisfy before a destructive
 * MySQL integration test is permitted to reset the database it's pointed
 * at. Real incident, not hypothetical: `PO-RC1-012` pointed `DB_DATABASE`
 * at the real development/demo MySQL database and `RefreshDatabase` wiped
 * it.
 *
 * Deliberately a positive allow-list rule, not a denylist of known-bad
 * names (`slipguard`, `slipguard_staging`, `slipguard_production`, ...) —
 * a denylist only protects the specific names someone thought to write
 * down; a name nobody has invented yet (a typo, a future environment) would
 * sail straight through it. "Unknown databases are unsafe by default"
 * requires the reverse: the database must affirmatively prove it's a test
 * database before anything destructive is allowed to touch it.
 *
 * Two independent conditions, both required (defense in depth): the
 * resolved database name must end in `_test`, AND the application
 * environment must be `testing`. Neither alone is sufficient — the
 * incident this guards against occurred with `APP_ENV` still correctly
 * `testing` (nothing overrode it) while only `DB_DATABASE` was pointed
 * somewhere unsafe, so an environment-only check would not have caught it;
 * conversely a database-name-only check gives no protection against a
 * `_test`-suffixed name being reused outside a genuine test run.
 *
 * A pure, framework-independent class deliberately — no Laravel facades,
 * no database connection, so its own rule is unit-testable directly with
 * plain strings, without ever touching a real database connection (see
 * `tests/Unit/Support/MySqlTestDatabaseGuardTest.php`).
 */
final class MySqlTestDatabaseGuard
{
    public static function isPermitted(string $database, string $environment): bool
    {
        if ($environment !== 'testing') {
            return false;
        }

        if ($database === '' || $database === '_test') {
            return false;
        }

        return str_ends_with($database, '_test');
    }

    /**
     * @throws RuntimeException when the database/environment pair is not
     *                          an explicitly designated test database.
     */
    public static function assertPermitted(string $database, string $environment): void
    {
        if (self::isPermitted($database, $environment)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Destructive MySQL integration tests require an explicitly designated test database '.
            '(APP_ENV must be "testing" and the database name must end in "_test"). '.
            'Resolved database [%s] in environment [%s] is not permitted.',
            $database,
            $environment,
        ));
    }
}
