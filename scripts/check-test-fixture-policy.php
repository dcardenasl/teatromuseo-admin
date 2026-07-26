<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$violations = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/tests'));

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $contents = (string) file_get_contents($path);
    if (str_contains($path, '/tests/contract/')) {
        continue;
    }

    if (preg_match('/Database::seeder|Seeder::class/', $contents) === 1) {
        $violations[] = $path . ': Admin tests must use API fixtures, not database seeders';
    }
}

if ($violations !== []) {
    fwrite(STDERR, implode(PHP_EOL, $violations) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Fixture policy passed: Admin tests are isolated from database seeders.\n");
