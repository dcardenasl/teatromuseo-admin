<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RichTextPreviewTest extends CIUnitTestCase
{
    public function testPreviewRendersDecodedHtmlMarkup(): void
    {
        $html = view('cms/block_types/previews/rich_text', [
            'config' => [
                'css_class' => 'custom-preview',
            ],
            'data' => [
                'content' => '&lt;p&gt;Texto &lt;strong&gt;enriquecido&lt;/strong&gt;&lt;/p&gt;',
            ],
        ]);

        $this->assertStringContainsString('custom-preview', $html);
        $this->assertStringContainsString('<p>Texto <strong>enriquecido</strong></p>', $html);
        $this->assertStringNotContainsString('&lt;p&gt;', $html);
    }
}
