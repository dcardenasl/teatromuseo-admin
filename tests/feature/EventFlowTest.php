<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Events\Services\EventApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class EventFlowTest extends CIUnitTestCase
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
        $result = $this->get('/admin/events/events');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/events/events');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.read']],
        ])->get('/admin/events/events');

        $result->assertStatus(200);
    }

    public function testShowRendersForAdmin(): void
    {
        $mock = $this->createMock(EventApiService::class);
        $mock->method('get')
            ->with('test-uuid')
            ->willReturn($this->apiSuccess([
                'id'          => 21,
                'uuid'        => 'test-uuid',
                'title'       => 'Evento de prueba',
                'event_type'  => 'festival',
                'description' => 'Descripción de prueba',
                'status'      => 'published',
            ]));

        Services::injectMock('eventApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.read']],
        ])->get('/admin/events/events/test-uuid');

        $result->assertStatus(200);
        $result->assertSee('Evento de prueba');
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.write']],
        ])->post('/admin/events/events', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testStoreSuccessInvalidatesPublicCache(): void
    {
        $mock = $this->createMock(EventApiService::class);
        $mock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['title'] ?? '') === 'Evento de prueba'
                    && ($payload['event_type'] ?? '') === 'festival'
                    && ($payload['description'] ?? '') === 'Descripción de prueba'
                    && ($payload['status'] ?? '') === 'published'
                    && ($payload['translations'] ?? []) === [];
            }))
            ->willReturn($this->apiSuccess(['id' => 21]));
        Services::injectMock('eventApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['events', 'event_types'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.write']],
        ])->post('/admin/events/events', [
            csrf_token() => csrf_hash(),
            'title'       => 'Evento de prueba',
            'event_type'  => 'festival',
            'description' => 'Descripción de prueba',
            'status'      => 'published',
        ]);

        $result->assertRedirectTo(site_url('admin/events/events'));
    }

    public function testUpdateSuccessInvalidatesPublicCache(): void
    {
        $mock = $this->createMock(EventApiService::class);
        $mock->expects($this->once())
            ->method('update')
            ->with('test-uuid', $this->callback(static function (array $payload): bool {
                return ($payload['title'] ?? '') === 'Evento de prueba'
                    && ($payload['event_type'] ?? '') === 'festival'
                    && ($payload['description'] ?? '') === 'Descripción actualizada'
                    && ($payload['status'] ?? '') === 'published'
                    && ($payload['translations'] ?? []) === [];
            }))
            ->willReturn($this->apiSuccess(['id' => 21]));
        Services::injectMock('eventApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['events', 'event_types'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.write']],
        ])->post('/admin/events/events/test-uuid', [
            csrf_token() => csrf_hash(),
            'title'       => 'Evento de prueba',
            'event_type'  => 'festival',
            'description' => 'Descripción actualizada',
            'status'      => 'published',
        ]);

        $result->assertRedirectTo(site_url('admin/events/events'));
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(EventApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn($this->apiSuccess());
        Services::injectMock('eventApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['events', 'event_types'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.events.delete']],
        ])->post('/admin/events/events/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/events/events'));
    }
}
