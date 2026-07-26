<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\EntryStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class EntryStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadKeepsFeaturedImagePerTranslationWhenSelectedFromTheHub(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'collection_id' => '12',
            'status' => 'published',
            'author_id' => '',
            'is_featured' => '1',
            'view_count' => '7',
            'sort_order' => '3',
            'is_in_sitemap' => '1',
            'sitemap_priority' => '0.7',
            'sitemap_changefreq' => 'weekly',
            'published_at' => '2026-06-27 10:00:00',
            'scheduled_at' => '',
            'translations' => [
                [
                    'language_id' => '1',
                    'slug' => 'hola-mundo',
                    'title' => 'Hola mundo',
                    'excerpt' => 'Resumen',
                    'featured_image' => ['source_kind' => 'hub_file', 'file_id' => '42', 'url' => ''],
                    'meta_title' => 'Meta title',
                    'meta_description' => 'Meta description',
                ],
            ],
        ]);

        $formRequest = new EntryStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame(12, $payload['collection_id']);
        $this->assertSame([
            [
                'language_id' => 1,
                'slug' => 'hola-mundo',
                'title' => 'Hola mundo',
                'excerpt' => 'Resumen',
                'featured_file_id' => 42,
                'featured_image_url' => '/files/42/view',
                'og_image_file_id' => null,
                'og_image_url' => null,
                'meta_title' => 'Meta title',
                'meta_description' => 'Meta description',
            ],
        ], $payload['translations']);
    }

    public function testPayloadAcceptsExternalUrlSourceForFeaturedAndOgImage(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'collection_id' => '12',
            'status' => 'published',
            'translations' => [
                [
                    'language_id' => '1',
                    'slug' => 'hola-mundo',
                    'title' => 'Hola mundo',
                    'featured_image' => ['source_kind' => 'external_url', 'file_id' => '', 'url' => 'https://cdn.example.com/cover.jpg'],
                    'og_image' => ['source_kind' => 'external_url', 'file_id' => '', 'url' => 'https://cdn.example.com/og.jpg'],
                ],
            ],
        ]);

        $formRequest = new EntryStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame([
            [
                'language_id' => 1,
                'slug' => 'hola-mundo',
                'title' => 'Hola mundo',
                'excerpt' => null,
                'featured_file_id' => null,
                'featured_image_url' => 'https://cdn.example.com/cover.jpg',
                'og_image_file_id' => null,
                'og_image_url' => 'https://cdn.example.com/og.jpg',
                'meta_title' => null,
                'meta_description' => null,
            ],
        ], $payload['translations']);
    }

    public function testTranslationRowWithOnlyAnOgImageIsKeptAsMeaningful(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'collection_id' => '12',
            'status' => 'published',
            'translations' => [
                [
                    'language_id' => '1',
                    'og_image' => ['file_id' => '9'],
                ],
            ],
        ]);

        $formRequest = new EntryStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertCount(1, $payload['translations']);
        $this->assertSame(9, $payload['translations'][0]['og_image_file_id']);
    }
}
