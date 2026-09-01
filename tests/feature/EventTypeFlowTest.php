<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Events\Services\EventTypeApiServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class EventTypeFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/events/event-types');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/events/event-types');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.read']],
        ])->get('/admin/events/event-types');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.write']],
        ])->post('/admin/events/event-types', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testCheckSlugReturnsAvailability(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('checkSlug')
            ->with('exhibitions', 'en', '')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['available' => true],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.read']],
        ])->get('/admin/events/event-types/check-slug?slug=exhibitions&locale=en');

        $result->assertStatus(200);
        $result->assertJSONFragment(['available' => true]);
    }

    public function testShowRendersNotFoundError(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('missing-uuid')
            ->willReturn([
                'ok' => false, 'status' => 404, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.read']],
        ])->get('/admin/events/event-types/missing-uuid');

        $result->assertStatus(200);
    }

    public function testEditRedirectsWhenNotFound(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('missing-uuid')
            ->willReturn([
                'ok' => false, 'status' => 404, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.write']],
        ])->get('/admin/events/event-types/missing-uuid/edit');

        $result->assertRedirectTo(site_url('admin/events/event-types'));
    }

    public function testReorderRendersForAdmin(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['items' => []],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.write']],
        ])->get('/admin/events/event-types/reorder');

        $result->assertStatus(200);
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $cacheMock = $this->createMock(\App\Libraries\PublicSiteCacheInvalidator::class);
        $cacheMock->expects($this->once())
            ->method('invalidate')
            ->with(['event_types'])
            ->willReturn(true);
        Services::injectMock('publicSiteCacheInvalidator', $cacheMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.delete']],
        ])->post('/admin/events/event-types/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/events/event-types'));
    }

    public function testDeleteFailureRedirectsBackWithError(): void
    {
        $mock = $this->createMock(EventTypeApiServiceInterface::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => false, 'status' => 409, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => ['In use'], 'fieldErrors' => [],
            ]);

        Services::injectMock('eventTypeApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['event.event-types.delete']],
        ])->post('/admin/events/event-types/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/events/event-types'));
    }
}
