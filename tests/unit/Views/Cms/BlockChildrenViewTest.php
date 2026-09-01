<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BlockChildrenViewTest extends CIUnitTestCase
{
    public function testRendersBothChildImagesFromThumbUrls(): void
    {
        $html = view('cms/pages/blocks/children/index', [
            'page' => ['id' => 17, 'title' => 'About'],
            'parentBlock' => ['id' => 3024],
            'parentType' => ['name' => 'Team', 'icon' => 'users'],
            'children' => [[
                'id' => 3009,
                'block_id' => 46,
                'sort_order' => 1,
                'is_active' => true,
                'block_config' => [
                    'photo' => [
                        'file_id' => 1775,
                        'thumb_url' => 'http://localhost:8180/uploads/primary_thumb.webp',
                        'url' => '/files/1775/view',
                    ],
                    'hover_photo' => [
                        'file_id' => 1772,
                        'thumb_url' => 'http://localhost:8180/uploads/hover_thumb.webp',
                        'url' => '/files/1772/view',
                    ],
                ],
                'translations' => [[
                    'block_data' => ['name' => 'Víctor Quiroga'],
                ]],
            ]],
            'blockTypes' => [46 => ['block_key' => 'team_member']],
            'collectionsMap' => [],
            'languages' => [],
            'blockTranslationStatus' => [],
            'ownerType' => 'page',
            'ownerLabel' => 'Página',
            'childLabel' => 'Miembro del Equipo',
            'childLabelPlural' => 'Miembros del equipo',
            'ownerBlocksRoute' => 'admin.cms.pages.blocks',
            'ownerCreateRoute' => 'admin.cms.pages.blocks.create',
            'ownerChildrenReorderRoute' => 'admin.cms.pages.blocks.children.reorder',
        ]);

        $this->assertSame(2, substr_count($html, 'data-preview-image='));
        $this->assertStringContainsString('data-preview-variant="thumb"', $html);
        $this->assertStringContainsString('primary_thumb.webp', $html);
        $this->assertStringContainsString('hover_thumb.webp', $html);
        $this->assertStringNotContainsString('/files/1775/view" alt=""', $html);
    }
}
