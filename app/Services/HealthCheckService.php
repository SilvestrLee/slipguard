<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckService
{
    public const STATUS_PASS = 'pass';

    public const STATUS_WARNING = 'warning';

    public const STATUS_FAIL = 'fail';

    /**
     * Run all diagnostic checks.
     *
     * @return array<string, array{status: string, message: string}>
     */
    public function run(): array
    {
        return [
            'Application Key' => $this->checkApplicationKey(),
            'Database' => $this->checkDatabase(),
            'Cache' => $this->checkCache(),
            'Queue Configuration' => $this->checkQueueConfiguration(),
            'Storage Writable' => $this->checkStorageWritable(),
            'Bootstrap Cache Writable' => $this->checkBootstrapCacheWritable(),
        ];
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkApplicationKey(): array
    {
        return filled(config('app.key'))
            ? $this->pass('Application key is set.')
            : $this->fail('Application key is missing.');
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->pass('Database connection is reachable.');
        } catch (Throwable) {
            return $this->fail('Database connection failed.');
        }
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkCache(): array
    {
        try {
            $key = 'slipguard-health-check';

            Cache::put($key, true, 5);
            $isReadable = Cache::get($key) === true;
            Cache::forget($key);

            return $isReadable
                ? $this->pass('Cache store is writable and readable.')
                : $this->fail('Cache store did not return the expected value.');
        } catch (Throwable) {
            return $this->fail('Cache store is unreachable.');
        }
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkQueueConfiguration(): array
    {
        $driver = config('queue.default');
        $connections = config('queue.connections', []);

        if (blank($driver) || ! isset($connections[$driver])) {
            return $this->fail('Default queue connection is not configured.');
        }

        return $driver === 'sync'
            ? $this->warning('Queue is running synchronously.')
            : $this->pass('Queue connection is configured.');
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkStorageWritable(): array
    {
        return is_writable(storage_path())
            ? $this->pass('Storage directory is writable.')
            : $this->fail('Storage directory is not writable.');
    }

    /**
     * @return array{status: string, message: string}
     */
    private function checkBootstrapCacheWritable(): array
    {
        return is_writable(app()->bootstrapPath('cache'))
            ? $this->pass('Bootstrap cache directory is writable.')
            : $this->fail('Bootstrap cache directory is not writable.');
    }

    /**
     * @return array{status: string, message: string}
     */
    private function pass(string $message): array
    {
        return ['status' => self::STATUS_PASS, 'message' => $message];
    }

    /**
     * @return array{status: string, message: string}
     */
    private function warning(string $message): array
    {
        return ['status' => self::STATUS_WARNING, 'message' => $message];
    }

    /**
     * @return array{status: string, message: string}
     */
    private function fail(string $message): array
    {
        return ['status' => self::STATUS_FAIL, 'message' => $message];
    }
}
