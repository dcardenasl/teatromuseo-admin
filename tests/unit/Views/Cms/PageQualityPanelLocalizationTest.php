<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guardrail for the page quality panel's i18n boundary.
 *
 * The CMS returns stable message keys; the Admin owns the localized copy.
 * This test prevents visible text from being added directly to this view and
 * requires every quality message to exist in both supported locales.
 */
final class PageQualityPanelLocalizationTest extends CIUnitTestCase
{
    /** @var list<string> */
    private const QUALITY_KEYS = [
        'title',
        'status_ready',
        'status_warning',
        'status_blocked',
        'status_unavailable',
        'score_title',
        'score_suffix',
        'summary_errors',
        'summary_warnings',
        'summary_passed',
        'h1_configured_title',
        'h1_configured_body',
        'h1_missing_title',
        'h1_missing_body',
        'h1_multiple_title',
        'h1_multiple_body',
        'action_default',
        'complete',
        'unavailable',
        'check_page_not_found',
        'check_field_configured',
        'check_default_title_required',
        'check_default_slug_required',
        'check_translation_title_missing',
        'check_translation_slug_missing',
        'check_meta_title_missing',
        'check_meta_description_missing',
        'check_field_length_valid',
        'check_meta_title_too_long',
        'check_meta_description_too_long',
        'check_schema_data_invalid',
        'check_schema_data_valid',
        'check_og_image_configured',
        'check_og_image_missing',
        'check_page_heading_missing',
        'check_page_heading_multiple',
        'check_page_heading_configured',
        'check_active_blocks_configured',
        'check_active_blocks_missing',
        'check_sitemap_robots_consistent',
        'check_sitemap_robots_inconsistent',
    ];

    public function testPageQualityPanelContainsNoLiteralVisibleText(): void
    {
        $path = APPPATH . 'Views/cms/pages/partials/quality_panel.php';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString("lang('PageQuality.", $source);
        self::assertStringNotContainsString("lang('Pages.", $source);
        $markup = preg_replace('/<\?(?:php|=).*?\?>/s', '', $source);
        $markup = preg_replace('/<!--.*?-->/s', '', (string) $markup);
        preg_match_all('/>\s*([^<]+?)\s*</u', (string) $markup, $matches);

        $visibleLiterals = array_values(array_filter(
            array_map(static fn (string $text): string => trim($text), $matches[1] ?? []),
            static fn (string $text): bool => $text !== '',
        ));

        self::assertSame(
            [],
            $visibleLiterals,
            "The page quality panel contains literal visible text. Use lang('PageQuality.*') instead:\n- "
            . implode("\n- ", $visibleLiterals),
        );
    }

    public function testPageQualityTranslationsHaveEsAndEnParity(): void
    {
        $es = require APPPATH . 'Modules/Cms/Language/es/PageQuality.php';
        $en = require APPPATH . 'Modules/Cms/Language/en/PageQuality.php';

        foreach (self::QUALITY_KEYS as $key) {
            self::assertArrayHasKey($key, $es, "Missing Spanish translation: {$key}");
            self::assertArrayHasKey($key, $en, "Missing English translation: {$key}");
            self::assertNotSame('', trim((string) $es[$key]), "Empty Spanish translation: {$key}");
            self::assertNotSame('', trim((string) $en[$key]), "Empty English translation: {$key}");
        }
    }

    public function testPageQualityTranslationsStayOutsideThePagesCatalog(): void
    {
        $esPages = require APPPATH . 'Modules/Cms/Language/es/Pages.php';
        $enPages = require APPPATH . 'Modules/Cms/Language/en/Pages.php';

        foreach (self::QUALITY_KEYS as $key) {
            self::assertArrayNotHasKey($key, $esPages, "Quality translation leaked into Spanish Pages catalog: {$key}");
            self::assertArrayNotHasKey($key, $enPages, "Quality translation leaked into English Pages catalog: {$key}");
        }
    }
}
