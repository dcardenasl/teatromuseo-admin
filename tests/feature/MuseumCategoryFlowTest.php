<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Museum\Services\CategoryApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class MuseumCategoryFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/museum/categories');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/museum/categories');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.read']],
        ])->get('/admin/museum/categories');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.create']],
        ])->post('/admin/museum/categories', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testStoreSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('create')
            ->willReturn([
                'ok' => true, 'status' => 201, 'data' => ['id' => 'test-uuid'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['categories'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.create']],
        ])->post('/admin/museum/categories', [
            csrf_token() => csrf_hash(),
            'name'       => 'Painting',
            'slug'       => 'painting',
        ]);

        $result->assertRedirectTo(site_url('admin/museum/categories'));
    }

    public function testShowRendersNotFoundError(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('missing-uuid')
            ->willReturn([
                'ok' => false, 'status' => 404, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.read']],
        ])->get('/admin/museum/categories/missing-uuid');

        $result->assertStatus(200);
    }

    public function testEditRedirectsWhenNotFound(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('missing-uuid')
            ->willReturn([
                'ok' => false, 'status' => 404, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.update']],
        ])->get('/admin/museum/categories/missing-uuid/edit');

        $result->assertRedirectTo(site_url('admin/museum/categories'));
    }

    public function testReorderRendersForAdmin(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => []],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.update']],
        ])->get('/admin/museum/categories/reorder');

        $result->assertStatus(200);
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['categories'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.delete']],
        ])->post('/admin/museum/categories/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/museum/categories'));
    }

    public function testDeleteFailureRedirectsBackWithError(): void
    {
        $mock = $this->createMock(CategoryApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => false, 'status' => 409, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => ['In use'], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumCategoryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['catalog.category.delete']],
        ])->post('/admin/museum/categories/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/museum/categories'));
    }
}
