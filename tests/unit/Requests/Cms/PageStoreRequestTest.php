<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\PageStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class PageStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadKeepsOgImagePerTranslationWhenSelectedFromTheHub(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'page_type' => 'generic',
            'status' => 'published',
            'translations' => [
                [
                    'language_id' => '1',
                    'slug' => 'inicio',
                    'title' => 'Inicio',
                    'og_image' => ['source_kind' => 'hub_file', 'file_id' => '7', 'url' => ''],
                ],
            ],
        ]);

        $formRequest = new PageStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame([
            [
                'language_id' => 1,
                'slug' => 'inicio',
                'title' => 'Inicio',
                'excerpt' => null,
                'og_image_file_id' => 7,
                'og_image_url' => '/files/7/view',
                'meta_title' => null,
                'meta_description' => null,
            ],
        ], $payload['translations']);
    }

    public function testPayloadAcceptsExternalUrlSourceForOgImage(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'page_type' => 'generic',
            'status' => 'published',
            'translations' => [
                [
                    'language_id' => '1',
                    'slug' => 'inicio',
                    'title' => 'Inicio',
                    'og_image' => ['source_kind' => 'external_url', 'file_id' => '', 'url' => 'https://cdn.example.com/og.jpg'],
                ],
            ],
        ]);

        $formRequest = new PageStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame(null, $payload['translations'][0]['og_image_file_id']);
        $this->assertSame('https://cdn.example.com/og.jpg', $payload['translations'][0]['og_image_url']);
    }

    public function testTranslationRowWithOnlyAnOgImageIsKeptAsMeaningful(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'page_type' => 'generic',
            'status' => 'published',
            'translations' => [
                [
                    'language_id' => '1',
                    'og_image' => ['file_id' => '3'],
                ],
            ],
        ]);

        $formRequest = new PageStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertCount(1, $payload['translations']);
        $this->assertSame(3, $payload['translations'][0]['og_image_file_id']);
    }
}
