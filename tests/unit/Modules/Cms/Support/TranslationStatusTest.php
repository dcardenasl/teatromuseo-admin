<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Support;

use App\Modules\Cms\Support\TranslationStatus;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TranslationStatusTest extends CIUnitTestCase
{
    public function testDefaultLanguageUsesCanonicalFields(): void
    {
        $result = TranslationStatus::evaluate(
            ['id' => 1, 'is_default' => true, '_source' => ['title' => 'Base', 'slug' => 'base']],
            [],
            ['title', 'slug'],
        );

        $this->assertSame('complete', $result['status']);
    }

    public function testDefaultLanguageWithARealTranslationRowIsNeverFlaggedMissing(): void
    {
        // Pages, Collections, Menus and Forms have no canonical title/name field
        // at all (confirmed against their response DTOs) — the default
        // language's content lives purely in a translation row like any other
        // language. A real row must win over an empty/absent `_source`.
        $language = ['id' => 1, 'is_default' => true, '_source' => []];
        $translations = [['language_id' => 1, 'title' => 'Inicio', 'slug' => 'inicio']];

        $result = TranslationStatus::evaluate($language, $translations, ['title', 'slug']);

        $this->assertSame('complete', $result['status']);
    }

    public function testDefaultLanguageMergesCanonicalFieldsIntoAPartialTranslationRow(): void
    {
        // Category/Tag denormalize the default language onto both a
        // canonical field and (potentially) a translation row; if the row
        // itself is missing a field, the canonical value still counts.
        $language = ['id' => 1, 'is_default' => true, '_source' => ['slug' => 'inicio']];
        $translations = [['language_id' => 1, 'title' => 'Inicio', 'slug' => '']];

        $result = TranslationStatus::evaluate($language, $translations, ['title', 'slug']);

        $this->assertSame('complete', $result['status']);
    }

    public function testSecondaryLanguageStatesAreCompleteIncompleteOrMissing(): void
    {
        $language = ['id' => 2, 'is_default' => false];
        $this->assertSame('missing', TranslationStatus::evaluate($language, [], ['title'])['status']);
        $this->assertSame('incomplete', TranslationStatus::evaluate($language, [['language_id' => 2, 'title' => '']], ['title'])['status']);
        $this->assertSame('complete', TranslationStatus::evaluate($language, [['language_id' => 2, 'title' => 'Translated']], ['title'])['status']);
    }

    public function testOlderCompleteTranslationIsOutdated(): void
    {
        $result = TranslationStatus::evaluate(
            ['id' => 2],
            [['language_id' => 2, 'title' => 'Translated', 'updated_at' => '2026-07-19 00:00:00']],
            ['title'],
            '2026-07-20 00:00:00',
        );

        $this->assertSame('outdated', $result['status']);
    }

    public function testBadgeClassesPillVariantCoversAllStatuses(): void
    {
        $this->assertSame('bg-red-100 text-red-700', TranslationStatus::badgeClasses('missing'));
        $this->assertSame('bg-orange-100 text-orange-700', TranslationStatus::badgeClasses('outdated'));
        $this->assertSame('bg-amber-100 text-amber-700', TranslationStatus::badgeClasses('incomplete'));
        $this->assertSame('bg-green-100 text-green-700', TranslationStatus::badgeClasses('complete'));
    }

    public function testBadgeClassesActionVariantNeverRendersComplete(): void
    {
        $this->assertSame('border-red-200 bg-red-50 text-red-800', TranslationStatus::badgeClasses('missing', 'action'));
        $this->assertSame('border-orange-200 bg-orange-50 text-orange-800', TranslationStatus::badgeClasses('outdated', 'action'));
        $this->assertSame('border-amber-200 bg-amber-50 text-amber-800', TranslationStatus::badgeClasses('incomplete', 'action'));
    }

    public function testEditUrlAppendsFocusLangHonoringExistingQueryString(): void
    {
        $this->assertSame('/edit?focus_lang=3', TranslationStatus::editUrl('/edit', 3));
        $this->assertSame('/edit?foo=1&focus_lang=3', TranslationStatus::editUrl('/edit?foo=1', 3));
        $this->assertSame('/edit', TranslationStatus::editUrl('/edit', 0));
    }
}
