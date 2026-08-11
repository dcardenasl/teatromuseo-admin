<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

final class FileDashboardLock implements DashboardLockInterface
{
    public function __construct(
        private readonly string $directory,
        private readonly int $maxAge = 120,
        private readonly int $waitMs = 250,
    ) {
    }

    public function acquire(string $key): ?string
    {
        if ($this->directory === '') {
            return null;
        }

        if (! is_dir($this->directory) && ! @mkdir($this->directory, 0750, true) && ! is_dir($this->directory)) {
            return null;
        }

        $path = rtrim($this->directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.lock';
        $deadline = microtime(true) + ($this->waitMs / 1000);

        do {
            $handle = @fopen($path, 'x');
            if ($handle !== false) {
                try {
                    $token = bin2hex(random_bytes(24));
                    if (fwrite($handle, $token) === false) {
                        @unlink($path);

                        return null;
                    }

                    return $token;
                } catch (\Throwable) {
                    @unlink($path);

                    return null;
                } finally {
                    fclose($handle);
                }
            }

            if ($this->isStale($path)) {
                @unlink($path);
                continue;
            }

            if (microtime(true) >= $deadline) {
                return null;
            }

            usleep(10_000);
        } while (true);
    }

    public function release(string $key, string $token): void
    {
        $path = rtrim($this->directory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.lock';
        $contents = @file_get_contents($path);

        if (is_string($contents) && hash_equals($contents, $token)) {
            @unlink($path);
        }
    }

    private function isStale(string $path): bool
    {
        $modifiedAt = @filemtime($path);

        return is_int($modifiedAt) && $modifiedAt < time() - max(1, $this->maxAge);
    }
}
