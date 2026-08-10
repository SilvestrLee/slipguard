<?php

use Tests\Support\MySqlTestDatabaseGuard;

/**
 * `AO-D1` (`PO-RC1-013`) — the guard's own rule, tested directly against
 * plain strings, no database connection involved at any point. Proves the
 * rule itself is correct without ever needing (or risking) real destructive
 * access to any database, safe or otherwise — exactly what `PO-RC1-013` §6
 * requires ("test the guard itself safely").
 */
test('an ordinary/production-shaped database name is rejected', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard', 'testing'))->toBeFalse();
});

test('a staging-shaped database name is rejected', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_staging', 'testing'))->toBeFalse();
});

test('a production-shaped database name is rejected even with the word test elsewhere in it', function () {
    // "contains test" is not the rule — only "ends with _test" is. A name
    // like this must not accidentally pass via a substring match.
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_test_production', 'testing'))->toBeFalse();
});

test('an unrelated/unknown database name is rejected by default', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('some_other_app', 'testing'))->toBeFalse();
});

test('an empty database name is rejected', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('', 'testing'))->toBeFalse();
});

test('the bare suffix alone with no prefix is rejected', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('_test', 'testing'))->toBeFalse();
});

test('an explicit test database name is permitted when the environment is testing', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_test', 'testing'))->toBeTrue();
});

test('any prefix ending in _test is permitted in the testing environment', function () {
    expect(MySqlTestDatabaseGuard::isPermitted('mysql_integration_test', 'testing'))->toBeTrue();
});

test('a correctly-named test database is still rejected outside the testing environment', function () {
    // Defense in depth — the real incident occurred with APP_ENV correctly
    // "testing" the whole time, so this axis alone would not have caught
    // it, but a *_test name reached outside a real test run is exactly the
    // scenario this second condition exists to close.
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_test', 'production'))->toBeFalse();
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_test', 'local'))->toBeFalse();
    expect(MySqlTestDatabaseGuard::isPermitted('slipguard_test', 'staging'))->toBeFalse();
});

test('assertPermitted throws a clear, credential-free error for a rejected database', function () {
    expect(fn () => MySqlTestDatabaseGuard::assertPermitted('slipguard', 'testing'))
        ->toThrow(RuntimeException::class, 'Destructive MySQL integration tests require an explicitly designated test database');

    try {
        MySqlTestDatabaseGuard::assertPermitted('slipguard', 'testing');
    } catch (RuntimeException $e) {
        expect($e->getMessage())
            ->toContain('slipguard')
            ->not->toContain('password')
            ->not->toContain('DB_PASSWORD')
            ->not->toContain('@'); // no host/user@host-shaped credential fragment
    }
});

test('assertPermitted does not throw for a permitted database', function () {
    MySqlTestDatabaseGuard::assertPermitted('slipguard_test', 'testing');

    expect(true)->toBeTrue();
});
