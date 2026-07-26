<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class EntryEditViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testEditViewShowsFeaturedImageFieldPerLanguage(): void
    {
        service('request')->setLocale('es');

        $html = view('cms/entries/edit', [
            'item' => [
                'id' => 99,
                'collection_id' => 5,
                'status' => 'draft',
                'workflow_status' => 'draft',
                'is_featured' => false,
                'translations' => [
                    [
                        'language_id' => 1,
                        'slug' => 'entrada-ejemplo',
                        'title' => 'Entrada ejemplo',
                        'excerpt' => 'Un resumen',
                        'featured_image' => ['source_kind' => 'hub_file', 'file_id' => 42, 'url' => '/files/42/view'],
                        'og_image' => ['source_kind' => 'external_url', 'file_id' => null, 'url' => 'https://cdn.example.com/og.jpg'],
                    ],
                    [
                        'language_id' => 2,
                        'slug' => 'sample-entry',
                        'title' => 'Sample entry',
                        'excerpt' => 'Summary',
                        'featured_image' => ['source_kind' => 'hub_file', 'file_id' => 42, 'url' => '/files/42/view'],
                        'og_image' => [],
                    ],
                ],
            ],
            'collections' => [
                5 => 'Blog',
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'es',
                ],
                [
                    'id' => 2,
                    'is_default' => false,
                    'code' => 'en',
                ],
            ],
            'defaultLangId' => 1,
            'defaultLangCode' => 'es',
            'defaultLangIndex' => 0,
            'blockTemplate' => null,
            'translateTargets' => [],
        ]);

        $this->assertStringContainsString('mediaReferenceField(', $html);
        $this->assertStringContainsString('Copiar a otros idiomas', $html);
        $this->assertStringContainsString('copyToAllLanguages()', $html);
        $this->assertStringContainsString('translations&#x5B;0&#x5D;&#x5B;featured_image&#x5D;&#x5B;url&#x5D;', $html);
        $this->assertStringContainsString('&#x2F;files&#x2F;42&#x2F;view', $html);
        $this->assertStringContainsString('cdn.example.com', $html);
    }
}
