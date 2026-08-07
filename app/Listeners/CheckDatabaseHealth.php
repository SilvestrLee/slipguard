<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * `OP-04` (RC1 Operations Programme) — closes `PO-MVP-005` finding R-20:
 * the framework's own `/up` route previously only confirmed the app booted,
 * not that it could reach its database. Laravel dispatches
 * `DiagnosingHealth` on every `/up` request and turns a thrown exception
 * into a 500 response — this listener is the officially-supported
 * extension point, not a custom route competing with the framework's own.
 */
class CheckDatabaseHealth
{
    public function handle(DiagnosingHealth $event): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            throw new RuntimeException('Database connection failed: '.$e->getMessage(), previous: $e);
        }
    }
}
