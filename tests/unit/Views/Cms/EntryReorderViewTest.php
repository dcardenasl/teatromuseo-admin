<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EntryReorderViewTest extends CIUnitTestCase
{
    public function testReorderViewBuildsTheScopedBatchSaveUrl(): void
    {
        $html = view('cms/entries/reorder', [
            'collections' => [7 => 'Noticias'],
            'selectedCollectionId' => 7,
            'items' => [
                ['id' => 11, 'title' => 'Primera entrada', 'slug' => 'primera-entrada'],
            ],
            'title' => 'Entradas - Orden',
        ]);

        $this->assertStringContainsString(
            '\\x3Fcollection_id\\x3D7',
            $html,
        );
        $this->assertStringContainsString('Primera entrada', $html);
        $this->assertStringNotContainsString('$saveOrderUrl', $html);
    }
}
