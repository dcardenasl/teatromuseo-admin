<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TranslationBadgesViewTest extends CIUnitTestCase
{
    public function testRendersLanguageBadgesForActiveLanguages(): void
    {
        $languages = [
            ['id' => 1, 'code' => 'es', 'name' => 'Spanish', 'is_default' => 1],
            ['id' => 2, 'code' => 'en', 'name' => 'English', 'is_default' => 0],
        ];

        $html = view('components/table/translation_badges', [
            'languages'      => $languages,
            'requiredFields' => ['title', 'slug'],
        ]);

        $this->assertStringContainsString('ES', $html);
        $this->assertStringContainsString('EN', $html);
        $this->assertStringContainsString('&#x5B;&quot;title&quot;,&quot;slug&quot;&#x5D;', $html);
        $this->assertStringContainsString('translationStatus(row, 1', $html);
        $this->assertStringContainsString('translationEditUrl(row.id, 1)', $html);
        $this->assertStringContainsString('translationEditUrl(row.id, 2)', $html);
    }
}
