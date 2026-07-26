<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Partials;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RichTextEditorTest extends CIUnitTestCase
{
    public function testEditorInitialValueIsEscapedForHtmlAttributeContext(): void
    {
        $html = view('partials/richtext_editor', [
            'fieldName' => 'translations[0][block_data][content]',
            'initialValue' => '<p class="lead">Hola</p>',
            'required' => true,
        ]);

        $this->assertStringContainsString("x-data='richTextEditor(", $html);
        $this->assertStringContainsString('data-richtext-toolbar', $html);
        $this->assertStringContainsString('data-richtext-action="bold"', $html);
        $this->assertStringNotContainsString('@click="bold()"', $html);
        $this->assertStringContainsString('value="&lt;p class=&quot;lead&quot;&gt;Hola&lt;/p&gt;"', $html);
        $this->assertStringContainsString('name="translations[0][block_data][content]"', $html);
    }
}
