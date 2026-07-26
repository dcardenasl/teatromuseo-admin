<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Smoke tests for collapsible page form sections
 *
 * @internal
 */
final class PageFormSectionsTest extends CIUnitTestCase
{
    public function testPageFormStructureHasCollapsibleSections(): void
    {
        // Verify that the form structure has Alpine.js x-data for sections
        // This is a simple smoke test to ensure the refactored sections exist

        // The view should initialize sections with:
        // x-data="{ expandedSections: { basic: true, translations: true, publishing: false, seo: false, advanced: false } }"
        // We just verify that the view file has these marker strings

        $filePath = APPPATH . 'Views/cms/pages/edit.php';
        $content = file_get_contents($filePath);

        // Verify Alpine.js data structure exists
        $this->assertStringContainsString('expandedSections', $content);
        $this->assertStringContainsString('basic: true', $content);
        $this->assertStringContainsString('translations: true', $content);

        // Verify section toggle buttons
        $this->assertStringContainsString('x-show="!expandedSections.basic"', $content);
        $this->assertStringContainsString('x-show="expandedSections.basic"', $content);

        // Verify translation status indicator calculation
        $this->assertStringContainsString('$completedCount', $content);
        $this->assertStringContainsString('$totalLanguages', $content);
    }

    public function testPageFormHasTranslationIndicators(): void
    {
        $filePath = APPPATH . 'Views/cms/pages/edit.php';
        $content = file_get_contents($filePath);

        // Verify translation status indicators (visual bullets)
        $this->assertStringContainsString("'complete'", $content);
        $this->assertStringContainsString("'incomplete'", $content);
        $this->assertStringContainsString("'missing'", $content);

        // Verify completion badge badge
        $this->assertStringContainsString('bg-brand-50', $content);

        // Verify language tabs structure
        $this->assertStringContainsString('langTabs', $content);
    }

    public function testPageFormHasSEOSectionPerLanguage(): void
    {
        $filePath = APPPATH . 'Views/cms/pages/edit.php';
        $content = file_get_contents($filePath);

        // Verify per-language SEO section exists
        $this->assertStringContainsString('section_seo_per_lang', $content);

        // Verify SEO form fields are included
        $this->assertStringContainsString('translation_meta_title_label', $content);
        $this->assertStringContainsString('translation_meta_description_label', $content);
    }

    public function testLanguageFilesHaveTranslationStatuses(): void
    {
        // Verify English language file has translation status strings
        $enFile = APPPATH . 'Modules/Cms/Language/en/Pages.php';
        $enContent = include($enFile);

        $this->assertArrayHasKey('translation_complete', $enContent);
        $this->assertArrayHasKey('translation_incomplete', $enContent);
        $this->assertArrayHasKey('translation_missing', $enContent);

        // Verify Spanish language file has translation status strings
        $esFile = APPPATH . 'Modules/Cms/Language/es/Pages.php';
        $esContent = include($esFile);

        $this->assertArrayHasKey('translation_complete', $esContent);
        $this->assertArrayHasKey('translation_incomplete', $esContent);
        $this->assertArrayHasKey('translation_missing', $esContent);
    }
}
