<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Services;

use App\Modules\Cms\Services\BlockCatalogService;
use App\Modules\Cms\Services\BlockTypeApiService;
use CodeIgniter\Test\CIUnitTestCase;

final class BlockCatalogServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cache()->delete('cms_block_types_active_catalog');
        cache()->delete('cms_block_types_template_catalog');
    }

    public function testReturnsEmptyCatalogWhenDomainIsUnavailable(): void
    {
        $blockTypes = $this->createMock(BlockTypeApiService::class);
        $blockTypes->method('list')->willThrowException(new \RuntimeException('Domain unavailable'));

        $this->assertSame([], (new BlockCatalogService($blockTypes))->all());
    }

    public function testSelectableFiltersMatchEntryPageAndTopLevelRules(): void
    {
        $blockTypes = $this->createMock(BlockTypeApiService::class);
        $blockTypes->method('list')->willReturn([
            'ok' => true,
            'status' => 200,
            'data' => [
                'data' => [
                    [
                        'id' => 1,
                        'block_key' => 'rich_text',
                        'name' => 'Rich text',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => true,
                        'is_child_only' => false,
                        'sort_order' => 1,
                    ],
                    [
                        'id' => 2,
                        'block_key' => 'page_header',
                        'name' => 'Page header',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => false,
                        'is_child_only' => false,
                        'sort_order' => 2,
                    ],
                    [
                        'id' => 3,
                        'block_key' => 'collection_grid',
                        'name' => 'Collection grid',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => false,
                        'is_child_only' => false,
                        'sort_order' => 3,
                    ],
                    [
                        'id' => 4,
                        'block_key' => 'hero_slider',
                        'name' => 'Hero slider',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => false,
                        'is_child_only' => false,
                        'sort_order' => 4,
                        'schema_definition' => json_encode([
                            'fields' => [],
                            'config_fields' => [],
                            'allowed_children' => ['slide_banner'],
                        ]),
                    ],
                    [
                        'id' => 5,
                        'block_key' => 'slide_banner',
                        'name' => 'Slide banner',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => false,
                        'is_child_only' => false,
                        'sort_order' => 5,
                    ],
                    [
                        'id' => 6,
                        'block_key' => 'container',
                        'name' => 'Container',
                        'is_active' => true,
                        'supports_pages' => true,
                        'supports_entries' => false,
                        'is_child_only' => false,
                        'sort_order' => 5,
                        'schema_definition' => json_encode([
                            'fields' => [],
                            'config_fields' => [],
                            'allowed_children' => ['rich_text', 'collection_grid'],
                        ]),
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ]);

        $service = new BlockCatalogService($blockTypes);

        $entrySelectable = $service->selectableForEntries();
        $pageSelectable = $service->selectableForPages();

        $this->assertCount(1, $entrySelectable);
        $this->assertSame('rich_text', $entrySelectable[0]['block_key']);

        $this->assertCount(6, $pageSelectable);
        $this->assertSame(['rich_text', 'page_header', 'collection_grid', 'hero_slider', 'container', 'slide_banner'], array_column($pageSelectable, 'block_key'));

        $topLevelSelectable = $service->selectableTopLevel();
        $this->assertSame(
            ['rich_text', 'page_header', 'collection_grid', 'hero_slider', 'container'],
            array_column($topLevelSelectable, 'block_key')
        );
    }

    public function testTemplatesReturnsApiCatalogWithoutExtraFiltering(): void
    {
        $blockTypes = $this->createMock(BlockTypeApiService::class);
        $blockTypes->method('templates')->willReturn([
            [
                'id' => 99,
                'block_key' => 'gallery_item',
                'name' => 'Gallery item',
                'is_active' => true,
            ],
        ]);

        $service = new BlockCatalogService($blockTypes);

        $this->assertSame('gallery_item', $service->templates()[0]['block_key']);
    }
}
