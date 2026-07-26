<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\BlockTypeApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class BlockTypeApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/block-types', [])
            ->willReturn($expected);

        $service = new BlockTypeApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/block-types/uuid-1')
            ->willReturn($expected);

        $service = new BlockTypeApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/block-types', $payload)
            ->willReturn($expected);

        $service = new BlockTypeApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/block-types/uuid-3')
            ->willReturn($expected);

        $service = new BlockTypeApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }

    public function testUsagesCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/block-types/uuid-4/usages')
            ->willReturn($expected);

        $service = new BlockTypeApiService($mock);
        $this->assertSame($expected, $service->usages('uuid-4'));
    }
}
