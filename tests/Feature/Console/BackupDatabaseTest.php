<?php

test('slipguard:backup-database refuses to run against a non-MySQL connection', function () {
    // The test suite's own connection is SQLite (phpunit.xml) — this proves
    // the command's safety guard actually fires rather than silently
    // mysqldump-ing against the wrong thing. Running it for real against
    // MySQL requires a real mysqldump binary and a real MySQL connection,
    // neither available in the standard test run — that path was verified
    // manually instead (see docs/08-operations/RC1_OPERATIONS_PROGRAMME.md
    // OP-05: a real backup was taken and all 23 tables' row counts were
    // cross-checked against the live database with zero mismatches).
    $this->artisan('slipguard:backup-database')
        ->assertFailed();
});
