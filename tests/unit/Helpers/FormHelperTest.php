<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FormHelperTest extends CIUnitTestCase
{
    public function testNormalizeMediaReferenceValueKeepsCanonicalExternalUrl(): void
    {
        helper('form');

        $value = normalize_media_reference_value([
            'source_kind' => 'external_url',
            'url' => 'https://cdn.example.com/image.jpg',
        ]);

        $this->assertSame('external_url', $value['source_kind']);
        $this->assertSame('', $value['file_id']);
        $this->assertSame('https://cdn.example.com/image.jpg', $value['url']);
    }

    public function testNormalizeMediaReferenceValueKeepsCanonicalHubFile(): void
    {
        helper('form');

        $value = normalize_media_reference_value([
            'source_kind' => 'hub_file',
            'file_id' => 42,
            'url' => '/files/42/view',
        ]);

        $this->assertSame('hub_file', $value['source_kind']);
        $this->assertSame('42', $value['file_id']);
        $this->assertSame('/files/42/view', $value['url']);
    }
}
