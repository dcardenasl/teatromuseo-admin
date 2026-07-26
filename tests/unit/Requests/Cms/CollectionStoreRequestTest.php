<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Cms;

use App\Modules\Cms\Requests\CollectionStoreRequest;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 */
final class CollectionStoreRequestTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testPayloadDropsEmptySecondaryTranslationsAndDerivesCollectionKey(): void
    {
        $request = service('request');
        $request->setGlobal('post', [
            'collection_type' => '',
            'collection_key' => '',
            'default_language_id' => '1',
            'default_sitemap_priority' => '',
            'default_changefreq' => '',
            'sort_order' => '4',
            'is_active' => '1',
            'requires_approval' => '0',
            'enables_categories' => '1',
            'enables_tags' => '0',
            'block_template' => '',
            'wizard_config' => '',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Coleccion prueba',
                    'slug' => 'coleccion-prueba',
                    'description' => 'Descripcion principal',
                ],
                [
                    'language_id' => '2',
                    'name' => '',
                    'slug' => '',
                    'description' => '',
                ],
            ],
        ]);

        $formRequest = new CollectionStoreRequest($request, service('validation'));
        $payload = $formRequest->payload();

        $this->assertSame('coleccion-prueba', $payload['collection_key']);
        $this->assertSame('other', $payload['collection_type']);
        $this->assertSame('0.5', $payload['default_sitemap_priority']);
        $this->assertSame('weekly', $payload['default_changefreq']);
        $this->assertSame(4, $payload['sort_order']);
        $this->assertSame('1', $payload['is_active']);
        $this->assertSame('0', $payload['requires_approval']);
        $this->assertSame('1', $payload['enables_categories']);
        $this->assertSame('0', $payload['enables_tags']);
        $this->assertSame([
            [
                'language_id' => 1,
                'slug' => 'coleccion-prueba',
                'name' => 'Coleccion prueba',
                'description' => 'Descripcion principal',
                'entry_cta_label' => null,
            ],
        ], $payload['translations']);
    }
}
