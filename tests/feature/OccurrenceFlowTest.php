<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
use App\Modules\Occurrences\Services\OccurrenceApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class OccurrenceFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/occurrences/occurrences');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/occurrences/occurrences');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.occurrences.read']],
        ])->get('/admin/occurrences/occurrences');

        $result->assertStatus(200);
    }

    public function testIndexLoadsBothLookupCatalogsThroughOneBffBundle(): void
    {
        $response = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'data' => [
                    'context' => 'occurrence',
                    'source' => ['event' => 'ok', 'state' => 'ok'],
                    'sections' => [
                        'events' => [['id' => 1, 'title' => 'Opening night']],
                        'venues' => [['id' => 2, 'name' => 'Main hall']],
                    ],
                ],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminEventLookups')
            ->with('occurrence')
            ->willReturn($response);
        Services::injectMock('bffApiClient', $bff);
        Services::resetSingle('eventLookupBffAdapter');

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.occurrences.read']],
        ])->get('/admin/occurrences/occurrences');

        $result->assertStatus(200);
        $this->assertStringContainsString('Opening night', $result->getBody());
        $this->assertStringContainsString('Main hall', $result->getBody());
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.occurrences.write']],
        ])->post('/admin/occurrences/occurrences', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(OccurrenceApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('occurrenceApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.occurrences.delete']],
        ])->post('/admin/occurrences/occurrences/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/occurrences/occurrences'));
    }
}
