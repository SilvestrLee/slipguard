<?php

use App\Listeners\CheckDatabaseHealth;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;

test('the health check passes silently when the database connection is reachable', function () {
    // The real test database (SQLite in-memory, per phpunit.xml) is reachable
    // throughout the suite — no mocking needed to prove the happy path.
    (new CheckDatabaseHealth)->handle(new DiagnosingHealth);
})->throwsNoExceptions();

test('the health check throws when the database connection fails, so /up reports 500', function () {
    DB::shouldReceive('connection->getPdo')->once()->andThrow(new PDOException('SQLSTATE[HY000] Connection refused'));

    expect(fn () => (new CheckDatabaseHealth)->handle(new DiagnosingHealth))
        ->toThrow(RuntimeException::class, 'Database connection failed');
});

test('/up returns 200 when the database is reachable', function () {
    $this->get('/up')->assertOk();
});
