<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Events\Services\EventTypeApiService;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class EventTypeApiServiceTest extends CIUnitTestCase
{
    public function testListUsesEventTypeCatalogueEndpoint(): void
    {
        $client = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];
        $client->expects($this->once())->method('get')->with('/events/event-types', ['page' => 1])->willReturn($expected);

        $this->assertSame($expected, (new EventTypeApiService($client))->list(['page' => 1]));
    }

    public function testCreateUpdateAndDeleteUseResourceEndpoint(): void
    {
        $client = $this->createMock(ApiClientInterface::class);
        $client->expects($this->once())->method('post')->with('/events/event-types', ['slug' => 'talk'])->willReturn(['ok' => true]);
        $client->expects($this->once())->method('put')->with('/events/event-types/7', ['name' => 'Talk'])->willReturn(['ok' => true]);
        $client->expects($this->once())->method('delete')->with('/events/event-types/7')->willReturn(['ok' => true]);
        $service = new EventTypeApiService($client);

        $service->create(['slug' => 'talk']);
        $service->update(7, ['name' => 'Talk']);
        $service->delete(7);

        $this->assertTrue(true);
    }
}
