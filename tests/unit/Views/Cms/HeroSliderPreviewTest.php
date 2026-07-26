<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * hero_slider is a container block: its real content lives in unlimited
 * "slide_banner" child block instances, not in flat slide_N_* fields on
 * this block. The local admin fallback preview (used only when the public
 * site is unreachable) never receives children data — see
 * blockPreview.js, which posts only block_config/block_data — so it
 * cannot honestly render slides and shows a clear message instead of a
 * hardcoded fake slide.
 *
 * @internal
 */
final class HeroSliderPreviewTest extends CIUnitTestCase
{
    public function testPreviewExplainsItNeedsThePublicSiteConnection(): void
    {
        $html = view('cms/block_types/previews/hero_slider', [
            'config' => [
                'caption_position' => 'overlay_bottom',
                'controls_position' => 'overlay_bottom',
            ],
            'data' => [],
        ]);

        $this->assertStringContainsString('sitio público', $html);
        $this->assertStringNotContainsString('slide_1_image_url', $html);
    }
}
