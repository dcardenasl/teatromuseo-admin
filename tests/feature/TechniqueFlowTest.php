<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Museum\Services\TechniqueApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class TechniqueFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/museum/techniques');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/museum/techniques');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['users.read']],
        ])->get('/admin/museum/techniques');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['users.read']],
        ])->post('/admin/museum/techniques', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(TechniqueApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('museumTechniqueApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['techniques'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['users.read']],
        ])->post('/admin/museum/techniques/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/museum/techniques'));
    }
}
