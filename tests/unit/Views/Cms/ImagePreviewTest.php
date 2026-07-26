<?php

declare(strict_types=1);

namespace Tests\Unit\Views\Cms;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The `image` block's `image` field is `config_fields.image` of type
 * `media_reference`, resolved server-side into the canonical nested shape
 * `{source_kind, file_id, url}` (see
 * BlockInstanceSerializer::mergeMediaReferenceField() in
 * ci4-website-builder-domain). This previously asserted the pre-migration
 * flat `data.image_url` key — updated to match the current contract.
 *
 * @internal
 */
final class ImagePreviewTest extends CIUnitTestCase
{
    public function testPreviewRendersTheResolvedImageUrlNotAPlaceholder(): void
    {
        $html = view('cms/block_types/previews/image', [
            'config' => [
                'image' => [
                    'source_kind' => 'external_url',
                    'file_id' => null,
                    'url' => 'https://cdn.example.com/photos/real-photo.jpg',
                ],
            ],
            'data' => [
                'alt' => 'A real photo',
                'caption' => 'A real caption',
            ],
        ]);

        $this->assertStringContainsString('https://cdn.example.com/photos/real-photo.jpg', $html);
        $this->assertStringNotContainsString('placehold.co', $html);
    }

    public function testPreviewFallsBackToPlaceholderWhenNoImageUrlIsResolvedYet(): void
    {
        $html = view('cms/block_types/previews/image', [
            'config' => [],
            'data' => [],
        ]);

        $this->assertStringContainsString('placehold.co', $html);
    }
}
