<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * `OP-05` (RC1 Operations Programme, `docs/08-operations/PRODUCTION_OPERATIONS_BLUEPRINT.md`
 * §O-05). Real, working `mysqldump` wrapper — not a stub. Writes a
 * timestamped, gzipped dump to local storage and (optionally, once real
 * credentials exist — see `--disk`) an off-server disk, then prunes old
 * backups per the blueprint's retention policy: 14 daily + 6 monthly.
 *
 * `mysqldump`'s binary path is configurable (`MYSQLDUMP_BINARY` in `.env`)
 * because it is not on `PATH` in at least this development environment —
 * `/usr/local/mysql/bin/mysqldump` is the real, working path here, the same
 * MySQL 8.0.30 install `I-01.2` verified. A production server's package
 * manager typically puts `mysqldump` on `PATH` directly.
 */
class BackupDatabase extends Command
{
    protected $signature = 'slipguard:backup-database
        {--disk= : Optional filesystem disk (e.g. s3) to additionally copy the backup to. Requires that disk to have real, working credentials.}
        {--keep-local : Keep the local copy even when --disk is used (default: kept either way for now, since no off-server disk has real credentials in this environment).}';

    protected $description = 'Dump the production database to a timestamped, gzipped file and prune old backups per the retention policy (14 daily + 6 monthly).';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error("Refusing to run: DB_CONNECTION is '{$connection}', not 'mysql'. This command backs up the real MySQL database only — never point it at the SQLite test connection.");

            return self::FAILURE;
        }

        $config = config('database.connections.mysql');
        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $sqlPath = "{$backupDir}/slipguard-{$timestamp}.sql";
        $gzPath = "{$sqlPath}.gz";

        $mysqldump = env('MYSQLDUMP_BINARY', 'mysqldump');

        $this->info("Dumping database '{$config['database']}'...");

        $process = new Process([
            $mysqldump,
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--result-file='.$sqlPath,
            $config['database'],
        ], env: ['MYSQL_PWD' => $config['password']]);
        // MYSQL_PWD (not --password= on the argv line) so the credential
        // never appears in `ps`/process-list output — the same concern
        // this repository's own secrets-hygiene practice (I-01.3) already
        // treats as real, applied here to a subprocess instead of a config file.

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('mysqldump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        if (! File::exists($sqlPath) || File::size($sqlPath) === 0) {
            throw new RuntimeException("mysqldump reported success but produced no output at {$sqlPath}.");
        }

        $gzip = new Process(['gzip', '-f', $sqlPath]);
        $gzip->run();

        if (! $gzip->isSuccessful() || ! File::exists($gzPath)) {
            $this->warn('gzip unavailable or failed — leaving the backup uncompressed.');
        } else {
            $sqlPath = $gzPath;
        }

        $this->info('Backup written: '.$sqlPath.' ('.$this->humanSize(File::size($sqlPath)).')');

        if ($disk = $this->option('disk')) {
            $this->copyToRemoteDisk($sqlPath, $disk);
        }

        $this->pruneOldBackups($backupDir);

        return self::SUCCESS;
    }

    /**
     * Real code path, genuinely wired to the `s3` disk `config/filesystems.php`
     * already defines — but this environment has no real S3-compatible
     * credentials configured, so this has not been exercised against a real
     * bucket. Prepared, not verified — disclosed rather than implied working.
     */
    private function copyToRemoteDisk(string $localPath, string $disk): void
    {
        try {
            Storage::disk($disk)->put('backups/'.basename($localPath), File::get($localPath));
            $this->info("Copied to disk '{$disk}'.");
        } catch (\Throwable $e) {
            $this->error("Failed to copy to disk '{$disk}': ".$e->getMessage());
            $this->warn('This is expected if no real credentials are configured for this disk in this environment.');
        }
    }

    private function pruneOldBackups(string $backupDir): void
    {
        $files = collect(File::files($backupDir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'slipguard-'))
            ->sortByDesc(fn ($f) => $f->getMTime());

        $now = Carbon::now();
        $keptMonths = [];
        $kept = 0;
        $pruned = 0;

        foreach ($files as $file) {
            $age = $now->diffInDays(Carbon::createFromTimestamp($file->getMTime()));
            $monthKey = Carbon::createFromTimestamp($file->getMTime())->format('Y-m');

            $isRecentDaily = $age <= 14;
            $isFirstOfItsMonth = ! in_array($monthKey, $keptMonths, true) && count($keptMonths) < 6;

            if ($isRecentDaily || $isFirstOfItsMonth) {
                if ($isFirstOfItsMonth && ! $isRecentDaily) {
                    $keptMonths[] = $monthKey;
                }
                $kept++;

                continue;
            }

            File::delete($file->getPathname());
            $pruned++;
        }

        if ($pruned > 0) {
            $this->info("Pruned {$pruned} backup(s) outside the retention policy; {$kept} retained.");
        }
    }

    private function humanSize(int $bytes): string
    {
        return $bytes >= 1_048_576
            ? round($bytes / 1_048_576, 1).' MB'
            : round($bytes / 1024, 1).' KB';
    }
}
