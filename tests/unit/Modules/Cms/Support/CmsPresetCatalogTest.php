<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Support;

use App\Modules\Cms\Support\CmsPresetCatalog;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * COL-003: filterAvailablePresets() used to drop a preset entirely the moment a single one of
 * its blocks referenced a block type absent from the deployment's active catalog. That's the
 * wrong default for a starter kit meant to grow — a real site customizing its block catalog
 * would silently lose starter presets with zero explanation. It now keeps the preset with just
 * the blocks that do exist, and reports which ones were dropped via `missing_blocks`.
 *
 * @internal
 */
final class CmsPresetCatalogTest extends CIUnitTestCase
{
    /**
     * @return array<int, array<string, mixed>>
     */
    private function presetsFixture(): array
    {
        return [
            [
                'type_key' => 'demo',
                'label' => 'Demo',
                'version' => '1.0',
                'block_template' => [
                    'version' => '1.0',
                    'blocks' => [
                        ['block_key' => 'rich_text', 'sort_order' => 1],
                        ['block_key' => 'page_header', 'sort_order' => 2],
                        ['block_key' => 'hero_banner', 'sort_order' => 3],
                    ],
                ],
                'wizard_config' => null,
            ],
        ];
    }

    public function testKeepsAllBlocksWhenEveryBlockTypeIsActive(): void
    {
        $result = CmsPresetCatalog::filterAvailablePresets(
            $this->presetsFixture(),
            ['rich_text', 'page_header', 'hero_banner']
        );

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]['missing_blocks']);
        $this->assertCount(3, $result[0]['block_template']['blocks']);
    }

    public function testKeepsPresetWithOnlyAvailableBlocksAndReportsMissingOnes(): void
    {
        $result = CmsPresetCatalog::filterAvailablePresets(
            $this->presetsFixture(),
            ['rich_text']
        );

        $this->assertCount(1, $result, 'the preset must survive with a partial block list, not disappear');
        $this->assertSame(['page_header', 'hero_banner'], $result[0]['missing_blocks']);
        $this->assertSame(
            ['rich_text'],
            array_column($result[0]['block_template']['blocks'], 'block_key')
        );
    }

    public function testDropsPresetEntirelyWhenNoneOfItsBlocksAreAvailable(): void
    {
        $result = CmsPresetCatalog::filterAvailablePresets(
            $this->presetsFixture(),
            ['image']
        );

        $this->assertSame([], $result);
    }

    public function testReturnsEmptyWhenNoBlockTypesAreActiveAtAll(): void
    {
        $result = CmsPresetCatalog::filterAvailablePresets($this->presetsFixture(), []);

        $this->assertSame([], $result);
    }

    public function testKeepsPresetWithoutBlocksAsIsAndDeclaresNothingMissing(): void
    {
        $presets = [
            [
                'type_key' => 'other',
                'label' => 'Other',
                'version' => '1.0',
                'block_template' => ['version' => '1.0', 'blocks' => []],
                'wizard_config' => null,
            ],
        ];

        $result = CmsPresetCatalog::filterAvailablePresets($presets, ['rich_text']);

        $this->assertCount(1, $result);
        $this->assertSame([], $result[0]['missing_blocks']);
    }
}
