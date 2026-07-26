<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\RedirectApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class RedirectFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/redirects');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/redirects');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.redirects.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/redirects');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.redirects.write']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/redirects', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(RedirectApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('redirectApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.redirects.write', 'cms.redirects.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/redirects/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/redirects'));
    }
}
