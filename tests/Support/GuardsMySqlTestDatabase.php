<?php

namespace Tests\Support;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * `AO-D1` (`PO-RC1-013`) — a drop-in replacement for Laravel's own
 * `RefreshDatabase` trait that refuses to run its destructive
 * `migrate:fresh` reset unless `MySqlTestDatabaseGuard` explicitly permits
 * the resolved database.
 *
 * Enforcement point matters here and was verified empirically, not
 * assumed: `RefreshDatabase::refreshDatabase()` is invoked from
 * `Illuminate\Foundation\Testing\Concerns\InteractsWithTestCaseLifecycle
 * ::setUpTraits()`, itself called from `TestCase::setUp()` — i.e. *before*
 * Pest's own `beforeEach()` closures ever run (those execute from inside
 * the generated test method body, which PHPUnit only invokes once
 * `setUp()` has already completed). A guard placed in a test file's own
 * `beforeEach()` would check too late — by then the migration has already
 * run. This trait instead overrides `refreshDatabase()` itself — the exact
 * method `setUpTraits()` calls — checks first, and only delegates to the
 * real (aliased) implementation if the check passes.
 *
 * The guard only engages for a `mysql` connection. When the active
 * connection is anything else (the default suite's in-memory sqlite, for
 * every one of this file's tests whenever the *full* suite runs), this
 * behaves exactly like the original `RefreshDatabase` — safe and expected,
 * since sqlite `:memory:` is never destructive to anything that matters.
 * This preserves this file's own existing `beforeEach()` behaviour
 * (`markTestSkipped()` when the driver isn't mysql) as a clean skip, not a
 * guard failure — the two checks are deliberately layered, not merged.
 */
trait GuardsMySqlTestDatabase
{
    use RefreshDatabase {
        refreshDatabase as private refreshDatabaseUnguarded;
    }

    public function refreshDatabase(): void
    {
        $connection = $this->app['db']->connection();

        if ($connection->getDriverName() === 'mysql') {
            MySqlTestDatabaseGuard::assertPermitted(
                $connection->getDatabaseName(),
                $this->app->environment(),
            );
        }

        $this->refreshDatabaseUnguarded();
    }
}
