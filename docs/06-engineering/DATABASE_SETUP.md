# Database Setup

| Field | Value |
|---|---|
| Standard database | MySQL 8.0+ |
| Test database | SQLite, in-memory (`phpunit.xml`) — unchanged, intentional |
| Verified | `I-01.2` (`PO-I01.2-001`), see `docs/engineering/I-01.2-MYSQL-MIGRATION-SAFETY-VERIFICATION.md` for full evidence |

---

MySQL 8.0+ is the standard local, staging and production database. SQLite remains the standard **test** database — the two are a deliberate, permanent split, not a migration in progress.

## 1. Install MySQL

Install MySQL Community Server 8.0 or later for your platform. Confirm it's running:

```bash
mysqladmin --host=127.0.0.1 --port=3306 -u root -p ping
```

## 2. Create the database and application user

Connect as root once, to create the application's own scoped account:

```sql
CREATE DATABASE slipguard
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'slipguard'@'localhost' IDENTIFIED BY 'choose-a-real-password';

GRANT ALL PRIVILEGES ON slipguard.*
TO 'slipguard'@'localhost';

FLUSH PRIVILEGES;
```

The application user is scoped to the `slipguard` database only — never grant it broader privileges, and never point the application at the MySQL root account.

## 3. Required PHP extensions

```bash
php -m | grep -Ei 'pdo|mysql'
```

Must include `PDO`, `pdo_mysql`, and `mysqli`.

## 4. Configure `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=slipguard
DB_USERNAME=slipguard
DB_PASSWORD=your-real-password
```

Never commit a real password. `.env` is gitignored; `.env.example` deliberately leaves `DB_PASSWORD` blank.

## 5. Migrate and seed

```bash
php artisan migrate
php artisan slipguard:demo --fresh   # optional — the canonical DW-01 demo workspace
```

## 6. Verify the active connection

```bash
php artisan config:clear
php artisan tinker --execute="dump(config('database.default')); dump(DB::connection()->getDatabaseName());"
```

Should print `"mysql"` and your configured database name.

## Testing database — intentionally separate

`phpunit.xml` hard-codes SQLite in-memory for the automated suite (`vendor/bin/pest` / `php artisan test`):

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This is intentional and unchanged by the MySQL adoption — fast, isolated, no shared state between test runs. A small, separate MySQL-specific integration suite (`tests/Feature/MySqlIntegrationTest.php`) exists for behaviour SQLite can't exercise identically (named constraint enforcement, native JSON validation) — it self-skips under any non-MySQL connection, so it never runs as part of the default suite. Run it explicitly against a real MySQL connection when needed:

```bash
php artisan test tests/Feature/MySqlIntegrationTest.php
```

Do not use the shared `slipguard` database for this — use a disposable database (see the evidence document referenced above for the exact procedure) so a failed run never corrupts real local data.

## Machine-specific note (not a project requirement)

The repository requires a working, supported PHP runtime (PHP 8.3+, per `composer.json`). On at least one development machine, the Herd-linked `php`/`php83` binaries fail at process start with a `dyld` symbol error (a broken local toolchain link, unrelated to this repository) — `/usr/local/bin/php` (Homebrew) is the working substitute there. This is a local environment workaround, not a universal project requirement; if your own `php` on `PATH` works correctly, use it as normal.

## PHP memory limit

A platform-default `memory_limit` of 128M is insufficient to run this application's own test suite (`PO-MVP-005` finding R-10 — two independent out-of-memory failures, Journal and History pagination). `phpunit.xml` now sets `memory_limit=1024M` for test runs specifically, so this doesn't require any local `php.ini` change to run `vendor/bin/pest`. If you run `artisan serve` or any other CLI command directly against a large dataset and hit a memory error, raise your CLI `php.ini`'s `memory_limit` (or pass `-d memory_limit=512M` per-command) — the same 512M minimum recommended for production (`docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md` §O-01) is a reasonable local floor too.

## Rollback

To return to SQLite:

```bash
# .env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

```bash
php artisan config:clear
php artisan tinker --execute="dump(config('database.default')); dump(DB::connection()->getDatabaseName());"
```

MySQL and SQLite can coexist — switching `.env` and clearing the config cache is the only step; no data automatically transfers between them in either direction. Any data created only in MySQL since the switch is not present in the SQLite file, and vice versa.
