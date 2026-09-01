<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
final class AlpineLifecycleTest extends CIUnitTestCase
{
    public function testViewsDoNotManuallyInvokeAlpineInitHook(): void
    {
        $violations = [];
        $views = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS),
        );

        foreach ($views as $view) {
            if (!$view->isFile() || $view->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($view->getPathname());
            if ($contents === false) {
                $violations[] = $view->getPathname() . ' (unreadable)';
                continue;
            }

            if (preg_match('~x-init\s*=\s*(["\'])\s*init\s*\(\s*\)\s*\1~i', $contents) === 1) {
                $violations[] = $view->getPathname();
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Views must rely on Alpine's automatic init() lifecycle hook; remove redundant x-init=\"init()\":\n"
            . implode("\n", $violations),
        );
    }
}
