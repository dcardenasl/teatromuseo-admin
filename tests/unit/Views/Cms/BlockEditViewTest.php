<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class BlockEditViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testEditViewKeepsOldInputAndShowsNestedFieldErrors(): void
    {
        helper(['form']);

        service('session')->set('_ci_old_input', [
            'post' => [
                'block_id' => '5',
                'sort_order' => '9',
                'is_active' => '0',
                'block_config' => [
                    'headline' => 'Submitted Headline',
                ],
                'translations' => [
                    [
                        'language_id' => '1',
                        'is_published' => '1',
                        'block_data' => [
                            'title' => 'Submitted Title',
                            'cover' => [
                                'source_kind' => 'hub_file',
                                'file_id' => '42',
                                'url' => '/files/42/view',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        service('session')->set('fieldErrors', [
            'block_config.headline' => 'Headline is required',
            'translations.0.block_data.title' => 'Title is required',
        ]);

        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => ['headline' => 'Existing Headline'],
                'translations' => [
                    [
                        'language_id' => 1,
                        'block_data' => [
                            'title' => 'Existing Title',
                            'cover' => [
                                'source_kind' => 'hub_file',
                                'file_id' => '42',
                                'url' => '/files/42/view',
                            ],
                        ],
                    ],
                ],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                        'required' => true,
                    ],
                    'cover' => [
                        'type' => 'media_reference',
                        'label' => 'Cover',
                        'accept' => 'image',
                        'required' => false,
                    ],
                ],
                'config_fields' => [
                    'headline' => [
                        'type' => 'string',
                        'label' => 'Headline',
                    ],
                ],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'en',
                ],
            ],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString('Submitted Headline', $html);
        $this->assertStringContainsString('Submitted Title', $html);
        $this->assertStringContainsString('Headline is required', $html);
        $this->assertStringContainsString('Title is required', $html);
        $this->assertStringContainsString('id="block-edit-form"', $html);
        $this->assertStringContainsString('data-language-id="1"', $html);
        $this->assertStringContainsString('mediaReferenceField(', $html);
        $this->assertStringContainsString('translations&#x5B;0&#x5D;&#x5B;block_data&#x5D;&#x5B;cover&#x5D;&#x5B;source_kind&#x5D;', $html);
        $this->assertStringContainsString('window.openBlockEditPreview', $html);
    }

    public function testEditViewRendersMediaReferenceRepeaters(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [],
            ],
            'blockType' => [
                'block_key' => 'gallery_gallery',
                'fields' => [
                    'gallery' => [
                        'type' => 'repeater',
                        'label' => 'Gallery',
                        'item_fields' => [
                            'poster' => [
                                'type' => 'media_reference',
                                'label' => 'Poster',
                                'accept' => 'image',
                            ],
                            'caption' => [
                                'type' => 'string',
                                'label' => 'Caption',
                            ],
                        ],
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'en',
                ],
            ],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString('mediaReferenceField(', $html);
    }

    public function testEditViewRendersCanonicalMediaReferenceFields(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [
                    [
                        'language_id' => 1,
                        'block_data' => [
                            'cover' => [
                                'source_kind' => 'hub_file',
                                'file_id' => 42,
                                'url' => '/files/42/view',
                            ],
                        ],
                    ],
                ],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'cover' => [
                        'type' => 'media_reference',
                        'label' => 'Cover',
                        'accept' => 'image',
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'en',
                ],
                [
                    'id' => 2,
                    'is_default' => false,
                    'code' => 'es',
                ],
            ],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString('mediaReferenceField(', $html);
        $this->assertStringContainsString('translations&#x5B;0&#x5D;&#x5B;block_data&#x5D;&#x5B;cover&#x5D;&#x5B;source_kind&#x5D;', $html);
        $this->assertStringNotContainsString('cover_file_id" x-model="fileId">', $html);
        $this->assertStringContainsString('Copiar a otros idiomas', $html);
    }

    public function testEditViewUsesDefaultLanguageCodeForAutoTranslate(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'pt',
                ],
                [
                    'id' => 2,
                    'is_default' => false,
                    'code' => 'en',
                ],
            ],
            'defaultLangId' => 1,
            'defaultLangCode' => 'pt',
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString("langTabs(1, '', 'PT')", $html);
        $this->assertStringNotContainsString("langTabs(1, '', 'ES')", $html);
    }

    public function testEditViewHidesAutoTranslateButtonWhenNoTargetsExist(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [],
            ],
            'blockType' => [
                'block_key' => 'hero',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'label' => 'Title',
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [
                [
                    'id' => 1,
                    'is_default' => true,
                    'code' => 'en',
                ],
            ],
            'translateTargets' => [],
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringNotContainsString('Traducir automáticamente', $html);
        $this->assertStringNotContainsString('autoTranslateAll(', $html);
    }

    public function testEditViewUsesMediaReferenceComponentPickerLabel(): void
    {
        $html = view('cms/pages/blocks/edit', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'block' => [
                'id' => 12,
                'block_id' => 5,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [],
                'translations' => [[
                    'language_id' => 1,
                    'block_data' => [
                        'documents' => [[
                            'file' => ['source_kind' => 'external_url', 'url' => 'https://example.com/demo.pdf'],
                        ]],
                    ],
                ]],
            ],
            'blockType' => [
                'block_key' => 'document_gallery',
                'fields' => [
                    'documents' => [
                        'type' => 'repeater',
                        'item_fields' => [
                            'file' => ['type' => 'media_reference', 'accept' => 'document'],
                        ],
                    ],
                ],
                'config_fields' => [],
            ],
            'languages' => [['id' => 1, 'is_default' => true, 'code' => 'es']],
            'defaultLangId' => 1,
            'defaultLangCode' => 'es',
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerUpdateRoute' => 'admin.cms.pages.blocks.update',
        ]);

        $this->assertStringContainsString('x-text="pickerButtonLabel()"', $html);
        $this->assertStringNotContainsString('pickerSelectLabels', $html);
        $this->assertStringNotContainsString('pickerChangeLabels', $html);
    }
}
