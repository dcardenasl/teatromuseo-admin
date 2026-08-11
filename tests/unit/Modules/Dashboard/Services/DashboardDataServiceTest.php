<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dashboard\Services;

use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClientInterface;
use App\Modules\Dashboard\Services\DashboardDataService;
use App\Modules\Dashboard\Services\DashboardLockInterface;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DashboardDataServiceTest extends CIUnitTestCase
{
    public function testColdReadCallsEachAggregateOnceAndCachesCombinedSnapshot(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);

        $hub->expects($this->once())
            ->method('request')
            ->with('GET', '/admin/dashboard/summary', ['max_retries' => 0], true)
            ->willReturn($this->response(['users' => ['total' => 4]]));
        $cms->expects($this->once())
            ->method('request')
            ->with('GET', '/cms/dashboard/summary', ['max_retries' => 0], true)
            ->willReturn($this->response(['counts' => ['pages' => 7]]));
        $catalog->expects($this->once())
            ->method('request')
            ->with('GET', '/catalog/dashboard/summary', ['max_retries' => 0], true)
            ->willReturn($this->response(['counts' => ['collection_items' => 11]]));
        $event->expects($this->once())
            ->method('request')
            ->with('GET', '/events/dashboard/summary', ['max_retries' => 0], true)
            ->willReturn($this->response(['counts' => ['events' => 3]]));

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $result  = $service->read(42, ['cms.pages.read', 'users.read']);

        $this->assertSame('fresh', $result['source']['state']);
        $this->assertSame([
            'hub' => ['users' => ['total' => 4]],
            'cms' => ['counts' => ['pages' => 7]],
            'catalog' => ['counts' => ['collection_items' => 11]],
            'event' => ['counts' => ['events' => 3]],
        ], $result['sections']);
        $this->assertTrue($this->hasKeyEndingIn($store, '_fresh'));
        $this->assertTrue($this->hasKeyEndingIn($store, '_stale'));
    }

    public function testFreshReadDoesNotCallUpstreamsAgain(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);
        $hub->expects($this->once())->method('request')->willReturn($this->response(['users' => ['total' => 1]]));
        $cms->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['pages' => 2]]));
        $catalog->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['collection_items' => 1]]));
        $event->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['events' => 1]]));

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $service->read(42, ['users.read']);
        $result = $service->read(42, ['users.read']);

        $this->assertSame('fresh', $result['source']['state']);
    }

    public function testUsesPerUpstreamStaleDataOnServerFailure(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);
        $hub->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['users' => ['total' => 1]]),
            $this->response([], 503, false),
        );
        $cms->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['pages' => 2]]),
            $this->response([], 503, false),
        );
        $catalog->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['collection_items' => 1]]),
            $this->response([], 503, false),
        );
        $event->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['events' => 1]]),
            $this->response([], 503, false),
        );

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $service->read(42, ['users.read']);
        $this->removeFreshEntries($store);
        $result = $service->read(42, ['users.read']);

        $this->assertSame('stale', $result['source']['state']);
        $this->assertSame('stale', $result['source']['hub']);
        $this->assertSame('stale', $result['source']['cms']);
        $this->assertSame(['users' => ['total' => 1]], $result['sections']['hub']);
        $this->assertSame(['counts' => ['pages' => 2]], $result['sections']['cms']);
    }

    public function testDoesNotMaskClientErrorWithStaleData(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);
        $hub->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['users' => ['total' => 1]]),
            $this->response([], 404, false),
        );
        $cms->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['pages' => 2]]),
            $this->response([], 404, false),
        );
        $catalog->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['collection_items' => 1]]),
            $this->response([], 404, false),
        );
        $event->expects($this->exactly(2))->method('request')->willReturnOnConsecutiveCalls(
            $this->response(['counts' => ['events' => 1]]),
            $this->response([], 404, false),
        );

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $service->read(42, ['users.read']);
        $this->removeFreshEntries($store);
        $result = $service->read(42, ['users.read']);

        $this->assertSame('unavailable', $result['source']['state']);
        $this->assertSame([], $result['sections']['hub']);
        $this->assertSame([], $result['sections']['cms']);
    }

    public function testFailureCooldownPreventsSequentialRetryStorm(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);
        $hub->expects($this->once())->method('request')->willReturn($this->response([], 503, false));
        $cms->expects($this->once())->method('request')->willReturn($this->response([], 503, false));
        $catalog->expects($this->once())->method('request')->willReturn($this->response([], 503, false));
        $event->expects($this->once())->method('request')->willReturn($this->response([], 503, false));

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $first   = $service->read(42, ['users.read']);
        $second  = $service->read(42, ['users.read']);

        $this->assertSame('unavailable', $first['source']['state']);
        $this->assertSame('unavailable', $second['source']['state']);
        $this->assertSame('failure_cooldown', $second['source']['reason']);
    }

    public function testBusyBuilderServesCombinedStaleSnapshotWithoutCallingUpstreams(): void
    {
        $store = [];
        $hub   = $this->createMock(ApiClientInterface::class);
        $cms   = $this->createMock(DomainApiClientInterface::class);
        $catalog = $this->createMock(DomainApiClientInterface::class);
        $event = $this->createMock(DomainApiClientInterface::class);
        $hub->expects($this->once())->method('request')->willReturn($this->response(['users' => ['total' => 1]]));
        $cms->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['pages' => 2]]));
        $catalog->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['collection_items' => 1]]));
        $event->expects($this->once())->method('request')->willReturn($this->response(['counts' => ['events' => 1]]));

        $service = $this->makeService($hub, $cms, $store, $catalog, $event);
        $service->read(42, ['users.read']);
        $this->removeFreshEntries($store);

        $busyLock = $this->createMock(DashboardLockInterface::class);
        $busyLock->expects($this->once())->method('acquire')->willReturn(null);
        $busyService = new DashboardDataService(
            $hub,
            $cms,
            $catalog,
            $event,
            $this->makeCache($store),
            $busyLock,
            300,
            3600,
            15,
            0,
        );

        $result = $busyService->read(42, ['users.read']);

        $this->assertSame('stale', $result['source']['state']);
        $this->assertSame('builder_busy', $result['source']['reason']);
    }

    /**
     * @param array<string, mixed> $store
     */
    private function makeService(
        ApiClientInterface $hub,
        DomainApiClientInterface $cms,
        array &$store,
        ?DomainApiClientInterface $catalog = null,
        ?DomainApiClientInterface $event = null,
    ): DashboardDataService {
        if ($catalog === null) {
            $catalog = $this->createMock(DomainApiClientInterface::class);
            $catalog->method('request')->willReturn($this->response(['counts' => []]));
        }
        if ($event === null) {
            $event = $this->createMock(DomainApiClientInterface::class);
            $event->method('request')->willReturn($this->response(['counts' => []]));
        }
        $lock = $this->createMock(DashboardLockInterface::class);
        $lock->method('acquire')->willReturn('lock-token');

        return new DashboardDataService(
            $hub,
            $cms,
            $catalog,
            $event,
            $this->makeCache($store),
            $lock,
            300,
            3600,
            15,
            0,
        );
    }

    /**
     * @param array<string, mixed> $store
     */
    private function makeCache(array &$store): CacheInterface
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(static function (string $key) use (&$store): mixed {
            return $store[$key] ?? null;
        });
        $cache->method('save')->willReturnCallback(static function (string $key, mixed $value, int $ttl) use (&$store): bool {
            $store[$key] = $value;

            return true;
        });

        return $cache;
    }

    /** @param array<string, mixed> $sections */
    private function response(array $sections, int $status = 200, bool $ok = true): array
    {
        return [
            'ok'          => $ok,
            'status'      => $status,
            'data'        => ['sections' => $sections],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }

    /** @param array<string, mixed> $store */
    private function removeFreshEntries(array &$store): void
    {
        foreach (array_keys($store) as $key) {
            if (str_ends_with($key, '_fresh')) {
                unset($store[$key]);
            }
        }
    }

    /** @param array<string, mixed> $store */
    private function hasKeyEndingIn(array $store, string $suffix): bool
    {
        foreach (array_keys($store) as $key) {
            if (str_ends_with($key, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
