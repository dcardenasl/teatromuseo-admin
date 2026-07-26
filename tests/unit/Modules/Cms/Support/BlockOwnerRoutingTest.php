<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Support;

use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Support\BlockOwnerRouting;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class BlockOwnerRoutingTest extends CIUnitTestCase
{
    private const SECRET = 'testing-preview-secret-at-least-32-characters';

    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();
    }

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPreviewUrlNeverCallsLanguageApiService(): void
    {
        // Regression guard: previewUrl() used to fetch languages via a fresh,
        // uncached API call on every invocation. It must now rely solely on
        // the $languages array passed in by the caller.
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->expects($this->never())->method('list');
        Services::injectMock('languageApiService', $languageMock);

        $page = [
            'id' => 7,
            'translations' => [
                ['language_id' => 1, 'slug' => 'nosotros'],
            ],
        ];

        $url = BlockOwnerRouting::previewUrl('page', $page, [
            ['id' => 1, 'code' => 'es'],
        ]);

        $this->assertStringContainsString('/es/nosotros', $url);
    }

    public function testPreviewUrlSignsLinksWhenSecretIsConfigured(): void
    {
        $page = [
            'id' => 7,
            'translations' => [
                ['language_id' => 1, 'slug' => 'nosotros'],
            ],
        ];

        $url = BlockOwnerRouting::previewUrl('page', $page, [
            ['id' => 1, 'code' => 'es'],
        ]);

        $this->assertStringContainsString('preview=1', $url);
        $this->assertMatchesRegularExpression('/preview_expires=\d+/', $url);
        $this->assertMatchesRegularExpression('/preview_sig=[0-9a-f]{64}/', $url);

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $expected = hash_hmac('sha256', 'page:es:nosotros:' . $query['preview_expires'], self::SECRET);
        $this->assertSame($expected, $query['preview_sig']);
    }

    public function testPreviewUrlDegradesToPlainLinkWithoutSecret(): void
    {
        putenv('CMS_PREVIEW_SECRET');
        unset($_ENV['CMS_PREVIEW_SECRET'], $_SERVER['CMS_PREVIEW_SECRET']);

        try {
            $page = [
                'id' => 7,
                'translations' => [
                    ['language_id' => 1, 'slug' => 'nosotros'],
                ],
            ];

            $url = BlockOwnerRouting::previewUrl('page', $page, [
                ['id' => 1, 'code' => 'es'],
            ]);

            $this->assertStringEndsWith('/es/nosotros', $url);
            $this->assertStringNotContainsString('preview', $url);
        } finally {
            putenv('CMS_PREVIEW_SECRET=' . self::SECRET);
            $_ENV['CMS_PREVIEW_SECRET'] = self::SECRET;
            $_SERVER['CMS_PREVIEW_SECRET'] = self::SECRET;
        }
    }

    public function testPreviewUrlForEntryStillMakesASingleTargetedCollectionLookup(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('get')
            ->with('3')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['id' => 3, 'collection_key' => 'blog', 'localized_slugs' => ['es' => 'blog']],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $entry = [
            'id' => 42,
            'collection_id' => 3,
            'translations' => [
                ['language_id' => 1, 'slug' => 'mi-entrada'],
            ],
        ];

        $url = BlockOwnerRouting::previewUrl('entry', $entry, [
            ['id' => 1, 'code' => 'es'],
        ]);

        $this->assertStringContainsString('/es/blog/mi-entrada', $url);
        $this->assertStringContainsString('preview_sig=', $url);
    }
}
