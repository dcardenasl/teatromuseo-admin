<?php

declare(strict_types=1);

namespace Tests\Unit\Views;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class FilesFiltersViewTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testFileFiltersOnlyExposeTheSharedSearchAndRangeControls(): void
    {
        $html = view('files/partials/filters', [
            'limitOptions' => [10, 25, 50, 100],
            'categoryOptions' => [
                ['value' => '', 'label' => 'Todos'],
                ['value' => 'image', 'label' => 'Imágenes'],
            ],
        ]);

        $this->assertStringContainsString('name="search"', $html);
        $this->assertStringContainsString('name="date_from"', $html);
        $this->assertStringContainsString('name="size_min"', $html);
        $this->assertStringNotContainsString('name="category"', $html);
    }
}
