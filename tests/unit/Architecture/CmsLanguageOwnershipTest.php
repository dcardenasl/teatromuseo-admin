<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guardrail for CMS language ownership.
 *
 * Each catalog is a seam for one coherent UI concern. This prevents the page
 * catalog from becoming a compatibility bucket for blocks or shared widgets.
 */
final class CmsLanguageOwnershipTest extends CIUnitTestCase
{
    /** @var list<string> */
    private const PAGE_ALLOWED_PREFIXES = [
        'pages_',
        'field_page_type',
        'page_type_',
        'field_collection_id',
        'field_status',
        'status_',
        'field_parent_id',
        'field_sort_order',
        'field_is_in_sitemap',
        'field_sitemap_',
        'sitemap_changefreq_',
        'field_published_at',
        'field_scheduled_at',
        'section_',
        'translation_title_',
        'translation_slug_',
        'translation_excerpt_',
        'translation_meta_',
        'translation_og_image_',
        'alert_translation_attention',
        'edit_in_language',
    ];

    /** @var list<string> */
    private const BLOCK_ALLOWED_PREFIXES = [
        'manage_blocks',
        'blocks_',
        'block_',
        'owner_',
        'child_',
        'children_',
    ];

    /** @var list<string> */
    private const CONTENT_TRANSLATION_KEYS = [
        'translations_title',
        'translations_help',
        'translation_label_default',
        'translation_complete',
        'translation_incomplete',
        'translation_missing',
    ];

    public function testPagesCatalogContainsOnlyPageOwnedKeys(): void
    {
        foreach (['es', 'en'] as $locale) {
            $catalog = require APPPATH . "Modules/Cms/Language/{$locale}/Pages.php";

            foreach (array_keys($catalog) as $key) {
                $owned = false;
                foreach (self::PAGE_ALLOWED_PREFIXES as $prefix) {
                    if (str_starts_with((string) $key, $prefix)) {
                        $owned = true;
                        break;
                    }
                }

                self::assertTrue($owned, "Pages.{$key} does not belong to the Pages catalog ({$locale}).");
            }
        }
    }

    public function testBlocksCatalogHasLocaleParityAndOnlyBlockOwnedKeys(): void
    {
        $es = require APPPATH . 'Modules/Cms/Language/es/Blocks.php';
        $en = require APPPATH . 'Modules/Cms/Language/en/Blocks.php';

        self::assertSame(array_keys($es), array_keys($en));

        foreach (array_keys($es) as $key) {
            $owned = false;
            foreach (self::BLOCK_ALLOWED_PREFIXES as $prefix) {
                if (str_starts_with((string) $key, $prefix)) {
                    $owned = true;
                    break;
                }
            }

            self::assertTrue($owned, "Blocks.{$key} does not belong to the Blocks catalog.");
        }
    }

    public function testContentTranslationsCatalogHasLocaleParityAndStableShape(): void
    {
        $es = require APPPATH . 'Language/es/ContentTranslations.php';
        $en = require APPPATH . 'Language/en/ContentTranslations.php';

        self::assertSame(self::CONTENT_TRANSLATION_KEYS, array_keys($es));
        self::assertSame(self::CONTENT_TRANSLATION_KEYS, array_keys($en));
    }

    public function testRootAndModuleLanguageNamespacesDoNotCollide(): void
    {
        $rootNamespaces = array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            glob(APPPATH . 'Language/es/*.php') ?: [],
        );
        $moduleNamespaces = array_map(
            static fn (string $path): string => pathinfo($path, PATHINFO_FILENAME),
            glob(APPPATH . 'Modules/*/Language/es/*.php') ?: [],
        );

        $collisions = array_values(array_intersect($rootNamespaces, $moduleNamespaces));
        sort($collisions);

        self::assertSame(
            [],
            $collisions,
            'A language namespace must have one owner. Collisions: ' . implode(', ', $collisions),
        );

        $moduleOwners = [];
        foreach (glob(APPPATH . 'Modules/*/Language/es/*.php') ?: [] as $path) {
            $moduleName = basename(dirname(dirname(dirname($path))));
            $namespace = pathinfo($path, PATHINFO_FILENAME);
            $moduleOwners[$namespace][] = $moduleName;
        }

        $duplicateModuleOwners = [];
        foreach ($moduleOwners as $namespace => $owners) {
            if (count($owners) > 1) {
                $duplicateModuleOwners[$namespace] = $owners;
            }
        }

        self::assertSame(
            [],
            $duplicateModuleOwners,
            'A module language namespace must have one module owner.',
        );
    }

    public function testLegacyPageNamespaceDoesNotLeakIntoBlockConsumers(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $appDir = $root . DIRECTORY_SEPARATOR . 'app';
        $violations = [];
        $pattern = '/Pages\\.(?:manage_blocks|blocks_|block_|owner_|child_|children_|translations_title|translations_help|translation_label_default|translation_complete|translation_incomplete|translation_missing)/';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir));

        foreach ($iterator as $file) {
            if (! $file instanceof \SplFileInfo || ! $file->isFile() || ! str_ends_with($file->getFilename(), '.php')) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if (! is_string($source) || $source === '') {
                continue;
            }

            if (preg_match($pattern, $source) === 1) {
                $relative = str_replace('\\', '/', ltrim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR));
                $violations[] = $relative;
            }
        }

        self::assertSame(
            [],
            $violations,
            "Legacy Pages language references remain in non-page concerns:\n- " . implode("\n- ", $violations),
        );
    }
}
