<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\DomainApiClientInterface;
use App\Services\SortOrderApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SortOrderApiServiceTest extends CIUnitTestCase
{
    public function testCmsUsesOneBatchRequestAndPreservesScope(): void
    {
        $cms = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $events = $this->createMock(DomainApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['updated' => 2]];
        $items = [
            ['id' => 11, 'sort_order' => 0],
            ['id' => 12, 'sort_order' => 1],
        ];
        $scope = ['collection_id' => 7];

        $cms->expects($this->once())
            ->method('post')
            ->with('/cms/sort-orders', [
                'resource' => 'entries',
                'items' => $items,
                'scope' => $scope,
            ])
            ->willReturn($expected);

        $service = new SortOrderApiService($cms, $catalog, $events);

        $this->assertSame($expected, $service->cms('entries', $items, $scope));
    }

    public function testCatalogUsesCatalogBatchEndpoint(): void
    {
        $cms = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $events = $this->createMock(DomainApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['updated' => 2]];
        $items = [
            ['id' => 21, 'sort_order' => 0],
            ['id' => 22, 'sort_order' => 1],
        ];

        $catalog->expects($this->once())
            ->method('post')
            ->with('/catalog/sort-orders', [
                'resource' => 'techniques',
                'items' => $items,
            ])
            ->willReturn($expected);

        $service = new SortOrderApiService($cms, $catalog, $events);

        $this->assertSame($expected, $service->catalog('techniques', $items));
    }

    public function testEventsUsesEventsBatchEndpoint(): void
    {
        $cms = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $events = $this->createMock(DomainApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['updated' => 2]];
        $items = [
            ['id' => 31, 'sort_order' => 0],
            ['id' => 32, 'sort_order' => 1],
        ];

        $events->expects($this->once())
            ->method('post')
            ->with('/events/sort-orders', [
                'resource' => 'event_types',
                'items' => $items,
            ])
            ->willReturn($expected);

        $service = new SortOrderApiService($cms, $catalog, $events);

        $this->assertSame($expected, $service->events('event_types', $items));
    }
}
