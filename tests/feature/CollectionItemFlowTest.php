<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use App\Modules\Museum\Services\CollectionItemApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class CollectionItemFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function apiSuccess(array $data = []): array
    {
        return [
            'ok'          => true,
            'status'      => 200,
            'data'        => $data,
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/museum/collection-items');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/museum/collection-items');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.read']],
        ])->get('/admin/museum/collection-items');

        $result->assertStatus(200);
    }

    public function testShowRendersForAdmin(): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCatalogCollectionItemWorkspace')
            ->with(15)
            ->willReturn($this->apiSuccess([
                'sections' => [
                    'collectionItem' => [
                        'id'             => 15,
                        'name'           => 'Ficha de prueba',
                        'inventory_code' => 'UIC-TEST',
                        'category_id'    => 7,
                        'status'         => 'published',
                        'translations'   => [],
                        'slug'           => 'ficha-de-prueba',
                    ],
                    'categories' => [['id' => 7, 'name' => 'Categoría de prueba']],
                    'languages' => [],
                ],
            ]));
        Services::injectMock('bffApiClient', $bff);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.read']],
        ])->get('/admin/museum/collection-items/15');

        $result->assertStatus(200);
        $result->assertSee('Ficha de prueba');
        $result->assertSee('UIC-TEST');
        $result->assertSee('ficha-de-prueba');
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.create']],
        ])->post('/admin/museum/collection-items', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testStoreSuccessInvalidatesPublicCache(): void
    {
        $mock = $this->createMock(CollectionItemApiService::class);
        $mock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return (int) ($payload['category_id'] ?? 0) === 7
                    && ($payload['inventory_code'] ?? '') === 'UIC-TEST'
                    && ($payload['status'] ?? '') === 'published'
                    && (int) ($payload['show_in_totem'] ?? 0) === 1
                    && (int) ($payload['is_active'] ?? 0) === 1;
            }))
            ->willReturn($this->apiSuccess(['id' => 15]));
        Services::injectMock('museumCollectionItemApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['collection_items'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.create']],
        ])->post('/admin/museum/collection-items', [
            csrf_token()      => csrf_hash(),
            'name'            => 'Ficha de prueba',
            'category_id'     => '7',
            'inventory_code'  => 'UIC-TEST',
            'status'          => 'published',
            'show_in_totem'   => '1',
            'is_active'       => '1',
        ]);

        $result->assertRedirectTo(site_url('admin/museum/collection-items'));
    }

    public function testUpdateSuccessInvalidatesPublicCache(): void
    {
        $mock = $this->createMock(CollectionItemApiService::class);
        $mock->expects($this->once())
            ->method('update')
            ->with('test-uuid', $this->callback(static function (array $payload): bool {
                return (int) ($payload['category_id'] ?? 0) === 7
                    && ($payload['inventory_code'] ?? '') === 'UIC-TEST'
                    && ($payload['status'] ?? '') === 'published'
                    && (int) ($payload['show_in_totem'] ?? 0) === 1
                    && (int) ($payload['is_active'] ?? 0) === 1;
            }))
            ->willReturn($this->apiSuccess(['id' => 15]));
        Services::injectMock('museumCollectionItemApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['collection_items'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.update']],
        ])->post('/admin/museum/collection-items/test-uuid', [
            csrf_token()      => csrf_hash(),
            'name'            => 'Ficha de prueba',
            'category_id'     => '7',
            'inventory_code'  => 'UIC-TEST',
            'status'          => 'published',
            'show_in_totem'   => '1',
            'is_active'       => '1',
        ]);

        $result->assertRedirectTo(site_url('admin/museum/collection-items'));
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(CollectionItemApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn($this->apiSuccess());
        Services::injectMock('museumCollectionItemApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['collection_items'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.collectionItem.delete']],
        ])->post('/admin/museum/collection-items/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/museum/collection-items'));
    }
}
