<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsTranslationsHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('cms_translations');
    }

    public function testBuildsOneMappingPerFieldWithIndexedTargets(): void
    {
        $languages = [['id' => 1], ['id' => 2], ['id' => 3]];

        $mappings = cms_translation_copy_mappings(['title', 'slug'], $languages, 0);

        $this->assertCount(2, $mappings);
        $this->assertSame('[name="translations[0][title]"]', $mappings[0]['source']);
        $this->assertSame([
            '[name="translations[0][title]"]',
            '[name="translations[1][title]"]',
            '[name="translations[2][title]"]',
        ], $mappings[0]['targets']);
        $this->assertSame('[name="translations[0][slug]"]', $mappings[1]['source']);
    }

    public function testUsesTheDefaultLanguageIndexAsSource(): void
    {
        $languages = [['id' => 1], ['id' => 2]];

        $mappings = cms_translation_copy_mappings(['name'], $languages, 1);

        $this->assertSame('[name="translations[1][name]"]', $mappings[0]['source']);
    }

    public function testReturnsEmptyTargetsWhenThereAreNoLanguages(): void
    {
        $mappings = cms_translation_copy_mappings(['name'], [], 0);

        $this->assertSame([], $mappings[0]['targets']);
    }
}
