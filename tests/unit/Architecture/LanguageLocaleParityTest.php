<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guardrail for translation catalogs across the two locales the admin UI
 * actually exposes (`Config\App::$supportedLocales`, currently `es`/`en`).
 *
 * A key present in one locale but missing in the other silently falls back
 * to CI4's raw-key rendering for that locale (e.g. `Menus.menus_title`
 * printed verbatim), which is a real, user-visible bug class in a
 * bilingual admin. This test catches both key drift and accidental blank
 * translations before they ship.
 */
final class LanguageLocaleParityTest extends CIUnitTestCase
{
    private const SUPPORTED_LOCALES = ['es', 'en'];

    /**
     * @return list<string> every directory that directly contains per-locale
     *                       Language catalogs (the app root, plus each module)
     */
    private function languageRoots(): array
    {
        $roots = [rtrim((string) APPPATH, '/\\') . '/Language'];

        foreach (glob(APPPATH . 'Modules/*/Language') ?: [] as $dir) {
            $roots[] = $dir;
        }

        sort($roots);

        return $roots;
    }

    public function testSupportedLocalesHaveIdenticalCatalogKeys(): void
    {
        $checked = 0;

        foreach ($this->languageRoots() as $root) {
            $primaryDir = $root . '/' . self::SUPPORTED_LOCALES[0];

            if (! is_dir($primaryDir)) {
                continue;
            }

            foreach (glob($primaryDir . '/*.php') ?: [] as $primaryFile) {
                $basename = basename($primaryFile);
                $primary  = require $primaryFile;
                self::assertIsArray($primary, "{$primaryFile} must return an array");

                foreach (array_slice(self::SUPPORTED_LOCALES, 1) as $locale) {
                    $otherFile = $root . '/' . $locale . '/' . $basename;

                    self::assertFileExists(
                        $otherFile,
                        "Missing '{$locale}' translation catalog for " . self::SUPPORTED_LOCALES[0] . "/{$basename} in {$root}",
                    );

                    $other = require $otherFile;
                    self::assertIsArray($other, "{$otherFile} must return an array");

                    $primaryKeys = array_keys($primary);
                    $otherKeys   = array_keys($other);
                    sort($primaryKeys);
                    sort($otherKeys);

                    $missing = array_diff($primaryKeys, $otherKeys);
                    $extra   = array_diff($otherKeys, $primaryKeys);

                    self::assertSame(
                        [],
                        $missing,
                        "{$basename} ({$locale}) is missing keys present in " . self::SUPPORTED_LOCALES[0] . ': ' . implode(', ', $missing),
                    );
                    self::assertSame(
                        [],
                        $extra,
                        "{$basename} ({$locale}) has keys not present in " . self::SUPPORTED_LOCALES[0] . ': ' . implode(', ', $extra),
                    );

                    $checked++;
                }
            }
        }

        self::assertGreaterThan(0, $checked, 'Expected at least one language catalog to be checked for parity.');
    }

    public function testSupportedLocalesHaveNoBlankStringTranslations(): void
    {
        $checked = 0;

        foreach ($this->languageRoots() as $root) {
            foreach (self::SUPPORTED_LOCALES as $locale) {
                $dir = $root . '/' . $locale;

                if (! is_dir($dir)) {
                    continue;
                }

                foreach (glob($dir . '/*.php') ?: [] as $file) {
                    $catalog = require $file;
                    self::assertIsArray($catalog, "{$file} must return an array");

                    foreach ($catalog as $key => $value) {
                        if (! is_string($value)) {
                            continue;
                        }

                        self::assertNotSame(
                            '',
                            trim($value),
                            basename($file) . ".{$key} ({$locale}) is blank",
                        );
                        $checked++;
                    }
                }
            }
        }

        self::assertGreaterThan(0, $checked, 'Expected at least one translation string to be checked.');
    }

    /**
     * Locale directories that exist beyond the officially supported ones
     * (e.g. Museum's `fr`/`pt`) are not wired into `LocaleFilter`, so they
     * are exempt from strict parity, but they must still parse cleanly.
     */
    public function testEveryLanguageCatalogParsesToNonEmptyArray(): void
    {
        $checked = 0;

        foreach ($this->languageRoots() as $root) {
            foreach (glob($root . '/*', GLOB_ONLYDIR) ?: [] as $localeDir) {
                foreach (glob($localeDir . '/*.php') ?: [] as $file) {
                    $catalog = require $file;
                    self::assertIsArray($catalog, "{$file} must return an array");
                    self::assertNotSame([], $catalog, "{$file} must not be an empty catalog");
                    $checked++;
                }
            }
        }

        self::assertGreaterThan(0, $checked, 'Expected at least one language catalog to be parsed.');
    }
}
