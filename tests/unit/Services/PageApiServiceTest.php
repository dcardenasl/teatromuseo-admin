<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PageApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/pages', [])
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/pages/uuid-1')
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/pages', $payload)
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/pages/uuid-3')
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }

    public function testPublishCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('put')
            ->with('/cms/pages/uuid-4', ['status' => 'published'])
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->publish('uuid-4'));
    }

    public function testArchiveCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('put')
            ->with('/cms/pages/uuid-5', ['status' => 'archived'])
            ->willReturn($expected);

        $service = new PageApiService($mock);
        $this->assertSame($expected, $service->archive('uuid-5'));
    }
}
