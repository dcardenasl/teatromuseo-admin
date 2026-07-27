<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Museum\Services\TechniqueApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TechniqueApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/catalog/techniques', [])
            ->willReturn($expected);

        $service = new TechniqueApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/catalog/techniques/uuid-1')
            ->willReturn($expected);

        $service = new TechniqueApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/catalog/techniques', $payload)
            ->willReturn($expected);

        $service = new TechniqueApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/catalog/techniques/uuid-3')
            ->willReturn($expected);

        $service = new TechniqueApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }
}
