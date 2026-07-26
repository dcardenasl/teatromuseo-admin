<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\MenuApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MenuApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/menus', [])
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/menus/uuid-1')
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/menus', $payload)
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/menus/uuid-3')
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }

    public function testListItemsCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/menu-items', ['menu_id' => 'uuid-1'])
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->listItems(['menu_id' => 'uuid-1']));
    }

    public function testGetItemCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/menu-items/uuid-item')
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->getItem('uuid-item'));
    }

    public function testCreateItemCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['label' => 'Test Item'];
        $expected = ['ok' => true, 'status' => 201, 'data' => []];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/menu-items', $payload)
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->createItem($payload));
    }

    public function testUpdateItemCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['label' => 'Test Item Updated'];
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('put')
            ->with('/cms/menu-items/uuid-item', $payload)
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->updateItem('uuid-item', $payload));
    }

    public function testDeleteItemCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/menu-items/uuid-item')
            ->willReturn($expected);

        $service = new MenuApiService($mock);
        $this->assertSame($expected, $service->deleteItem('uuid-item'));
    }
}
