<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Regression guard for the class of bug found 2026-07-17: block types whose
 * `media_reference` field lives in `config_fields` (per
 * ci4-website-builder-domain's CmsBlockTypeSeeder) but whose local admin
 * fallback preview (app/Views/cms/block_types/previews/*.php, used only
 * when the public site is unreachable — see BlockPreviewController) reads
 * it from `data` instead, silently always falling back to a placeholder.
 *
 * This fixture must be kept in sync with domain's CmsBlockTypeSeeder. If a
 * block type gains a new media_reference config field, add it here so this
 * test forces the matching fallback preview to be written/updated instead
 * of drifting silently.
 *
 * @internal
 */
final class BlockPreviewFieldCoverageTest extends CIUnitTestCase
{
    /**
     * block_key => media_reference field name under config_fields.
     * Repeater fields (document_gallery.fields.documents[].file,
     * video_gallery.fields.videos[].poster) are covered by existence only,
     * not here — their shape doesn't fit this generic single-field check.
     *
     * @var array<string, string>
     */
    private const CONFIG_MEDIA_REFERENCE_FIELDS = [
        'asset_item'    => 'logo',
        'card_item'     => 'image',
        'document_download' => 'document',
        'gallery_item'  => 'image',
        'hero_banner'   => 'image',
        'image'         => 'image',
        'pdf_viewer'    => 'pdf_file',
        'slide_banner'  => 'image',
        'slide_card'    => 'image',
        'team_member'   => 'photo',
        'timeline_item' => 'image',
        'video_player'  => 'poster',
    ];

    /**
     * block_key => preview file existence only (repeater-shaped fields).
     *
     * @var list<string>
     */
    private const REPEATER_MEDIA_BLOCK_KEYS = [
        'document_gallery',
        'video_gallery',
    ];

    public function testEveryConfigMediaReferenceFieldIsReadFromConfigNotData(): void
    {
        $testUrl = 'https://cdn.example.com/coverage-guard-test-image.jpg';

        foreach (self::CONFIG_MEDIA_REFERENCE_FIELDS as $blockKey => $fieldName) {
            $viewPath = "cms/block_types/previews/{$blockKey}";
            $this->assertFileExists(
                APPPATH . "Views/{$viewPath}.php",
                "Missing fallback preview view for block type '{$blockKey}'."
            );

            $html = view($viewPath, [
                'config' => [
                    $fieldName => [
                        'source_kind' => 'external_url',
                        'file_id' => null,
                        'url' => $testUrl,
                    ],
                ],
                'data' => [],
            ]);

            $this->assertStringContainsString(
                $testUrl,
                $html,
                "Block '{$blockKey}' did not render the media_reference url from "
                . "config['{$fieldName}'] — it likely still reads from \$data instead of \$config."
            );
        }
    }

    public function testEveryRepeaterMediaBlockHasAFallbackPreview(): void
    {
        foreach (self::REPEATER_MEDIA_BLOCK_KEYS as $blockKey) {
            $this->assertFileExists(
                APPPATH . "Views/cms/block_types/previews/{$blockKey}.php",
                "Missing fallback preview view for block type '{$blockKey}'."
            );
        }
    }
}
