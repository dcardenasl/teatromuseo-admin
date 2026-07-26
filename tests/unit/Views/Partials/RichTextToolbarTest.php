<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Partials;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RichTextToolbarTest extends CIUnitTestCase
{
    public function testToolbarUsesSharedDataAttributesForAllCommands(): void
    {
        $html = view('partials/richtext_toolbar');

        $this->assertStringContainsString('data-richtext-toolbar', $html);
        $this->assertStringContainsString('data-richtext-action="bold"', $html);
        $this->assertStringContainsString('data-richtext-action="heading"', $html);
        $this->assertStringContainsString('data-richtext-level="2"', $html);
        $this->assertStringContainsString('data-richtext-action="link"', $html);
        $this->assertStringContainsString('data-richtext-action="undo"', $html);
        $this->assertStringContainsString('data-richtext-action="redo"', $html);
        $this->assertStringNotContainsString('@click=', $html);
    }
}
