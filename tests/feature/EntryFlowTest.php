<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\EntryApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class EntryFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/entries');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/entries');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $mock = $this->createMock(EntryApiService::class);
        $mock->method('collections')->willReturn([
            'ok' => true, 'status' => 200, 'data' => ['items' => []],
            'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => []
        ]);
        Services::injectMock('entryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.entries.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/entries');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.entries.write']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/entries', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(EntryApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('entryApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.entries.write', 'cms.entries.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/entries/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/entries'));
    }

    public function testUpdateSynchronizesCategoriesAndTags(): void
    {
        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->expects($this->once())
            ->method('update')
            ->with('7', $this->isType('array'))
            ->willReturn($this->okResponse());
        $entryMock->expects($this->once())
            ->method('syncTaxonomy')
            ->with('7', [2, 5], [3])
            ->willReturn($this->okResponse());
        Services::injectMock('entryApiService', $entryMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user' => ['permissions' => ['cms.entries.write', 'cms.entries.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/entries/7', [
            csrf_token() => csrf_hash(),
            'collection_id' => '1',
            'status' => 'draft',
            'category_ids' => ['2', '5'],
            'tag_ids' => ['3'],
        ]);

        $result->assertRedirectTo(site_url('admin/cms/entries'));
    }

    /** @return array<string, mixed> */
    private function okResponse(): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => [],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
