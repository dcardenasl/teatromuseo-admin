<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class BlockCreateViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testCreateViewRendersMediaReferenceSelectorsForBlocks(): void
    {
        $html = view('cms/pages/blocks/create', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'blockTypes' => [
                [
                    'id' => 5,
                    'block_key' => 'gallery_item',
                    'name' => 'Gallery Item',
                    'schema_definition' => [
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
                        'config_fields' => [
                            'image' => [
                                'type' => 'media_reference',
                                'label' => 'Image',
                                'accept' => 'image',
                            ],
                        ],
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
            'ownerStoreRoute' => 'admin.cms.pages.blocks.store',
            'translateUrl' => '',
            'defaultLangCode' => 'en',
        ]);

        $this->assertStringContainsString('mediaReferenceField(', $html);
        $this->assertStringContainsString('block_config', $html);
    }

    public function testCreateViewRendersImageRepeatersAndCopyableMediaReferences(): void
    {
        $html = view('cms/pages/blocks/create', [
            'page' => ['id' => 21, 'title' => 'Page 21'],
            'blockTypes' => [
                [
                    'id' => 5,
                    'block_key' => 'hero_gallery',
                    'name' => 'Hero Gallery',
                    'schema_definition' => [
                        'fields' => [
                            'hero_image' => [
                                'type' => 'media_reference',
                                'label' => 'Hero image',
                                'accept' => 'image',
                            ],
                            'gallery' => [
                                'type' => 'repeater',
                                'label' => 'Gallery',
                                'item_fields' => [
                                    'poster' => [
                                        'type' => 'file',
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
                ],
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
            'ownerStoreRoute' => 'admin.cms.pages.blocks.store',
            'translateUrl' => '',
            'defaultLangCode' => 'en',
        ]);

        $this->assertStringContainsString('Copiar a otros idiomas', $html);
        $this->assertStringContainsString('mediaReferenceField(', $html);
        $this->assertStringContainsString("x-data=\"mediaReferenceField(field.default || {}, field.accept || 'image', fieldKey)\"", $html);
        $this->assertStringContainsString("x-data=\"mediaReferenceField(item[subKey] || {}, subField.accept || 'image')\"", $html);
        $this->assertStringContainsString('[source_kind]', $html);
        $this->assertStringContainsString('[file_id]', $html);
        $this->assertStringContainsString('[url]', $html);
    }
}
