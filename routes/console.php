<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// OP-05 (RC1 Operations Programme) — daily database backup, per
// docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md §O-05's retention
// policy (the command itself prunes to 14 daily + 6 monthly on each run).
// Safe to leave scheduled in every environment: BackupDatabase::handle()
// refuses to run unless DB_CONNECTION=mysql, so this is inert against the
// local SQLite test/dev setup and only takes effect once a real
// production `.env` (mysql) and a real cron -> `schedule:run` exist
// (ops/provision-server.sh's own follow-up steps) — neither exists yet.
Schedule::command('slipguard:backup-database')->dailyAt('02:00');
