<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Dashboard\Services;

use App\Libraries\BffApiClientInterface;
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
    public function testColdReadCallsBffOnceAndCachesCombinedSnapshot(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->with(0)
            ->willReturn($this->bffResponse([
                'hub' => ['users' => ['total' => 4]],
                'cms' => ['counts' => ['pages' => 7]],
                'catalog' => ['counts' => ['collection_items' => 11]],
                'event' => ['counts' => ['events' => 3]],
            ]));

        $result = $this->makeService($bff, $store)->read(42, ['cms.pages.read', 'users.read']);

        $this->assertSame('fresh', $result['source']['state']);
        $this->assertSame('fresh', $result['source']['hub']);
        $this->assertSame('fresh', $result['source']['cms']);
        $this->assertSame('fresh', $result['source']['catalog']);
        $this->assertSame('fresh', $result['source']['event']);
        $this->assertSame([
            'hub' => ['users' => ['total' => 4]],
            'cms' => ['counts' => ['pages' => 7]],
            'catalog' => ['counts' => ['collection_items' => 11]],
            'event' => ['counts' => ['events' => 3]],
        ], $result['sections']);
        $this->assertTrue($this->hasKeyEndingIn($store, '_fresh'));
        $this->assertTrue($this->hasKeyEndingIn($store, '_stale'));
    }

    public function testFreshReadDoesNotCallBffAgain(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->bffResponse([
                'hub' => ['users' => ['total' => 1]],
                'cms' => ['counts' => ['pages' => 2]],
                'catalog' => ['counts' => ['collection_items' => 1]],
                'event' => ['counts' => ['events' => 1]],
            ]));

        $service = $this->makeService($bff, $store);
        $service->read(42, ['users.read']);
        $result = $service->read(42, ['users.read']);

        $this->assertSame('fresh', $result['source']['state']);
    }

    public function testUsesCombinedStaleDataWhenBffHasServerFailure(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->exactly(2))
            ->method('getAdminDashboard')
            ->willReturnOnConsecutiveCalls(
                $this->bffResponse([
                    'hub' => ['users' => ['total' => 1]],
                    'cms' => ['counts' => ['pages' => 2]],
                    'catalog' => ['counts' => ['collection_items' => 1]],
                    'event' => ['counts' => ['events' => 1]],
                ]),
                $this->bffResponse([], 503, false),
            );

        $service = $this->makeService($bff, $store);
        $service->read(42, ['users.read']);
        $this->removeFreshEntries($store);
        $result = $service->read(42, ['users.read']);

        $this->assertSame('stale', $result['source']['state']);
        $this->assertSame('stale', $result['source']['hub']);
        $this->assertSame('stale', $result['source']['cms']);
        $this->assertSame(['users' => ['total' => 1]], $result['sections']['hub']);
        $this->assertSame(['counts' => ['pages' => 2]], $result['sections']['cms']);
    }

    public function testMapsPartialBffSourcesToTheExistingAdminStates(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->bffResponse([
                'hub' => ['users' => ['total' => 4]],
                'cms' => [],
                'catalog' => ['counts' => ['collection_items' => 12]],
                'event' => ['counts' => ['events' => 3]],
            ], 200, true, [
                'cms' => 'unavailable',
            ]));

        $result = $this->makeService($bff, $store)->read(42, ['users.read']);

        $this->assertSame('stale', $result['source']['state']);
        $this->assertSame('fresh', $result['source']['hub']);
        $this->assertSame('unavailable', $result['source']['cms']);
        $this->assertSame('fresh', $result['source']['catalog']);
        $this->assertSame('fresh', $result['source']['event']);
        $this->assertSame([], $result['sections']['cms']);
        $this->assertSame(['counts' => ['collection_items' => 12]], $result['sections']['catalog']);
    }

    public function testDoesNotMaskBffClientErrorWithStaleData(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->bffResponse([], 404, false));

        $result = $this->makeService($bff, $store)->read(42, ['users.read']);

        $this->assertSame('unavailable', $result['source']['state']);
        $this->assertSame('unavailable', $result['source']['hub']);
        $this->assertSame('unavailable', $result['source']['cms']);
        $this->assertSame([], $result['sections']['hub']);
        $this->assertSame([], $result['sections']['cms']);
    }

    public function testFailureCooldownPreventsSequentialBffRetryStorm(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->bffResponse([], 503, false));

        $service = $this->makeService($bff, $store);
        $first   = $service->read(42, ['users.read']);
        $second  = $service->read(42, ['users.read']);

        $this->assertSame('unavailable', $first['source']['state']);
        $this->assertSame('unavailable', $second['source']['state']);
        $this->assertSame('failure_cooldown', $second['source']['reason']);
    }

    public function testBusyBuilderServesCombinedStaleSnapshotWithoutCallingBff(): void
    {
        $store = [];
        $bff   = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->bffResponse([
                'hub' => ['users' => ['total' => 1]],
                'cms' => ['counts' => ['pages' => 2]],
                'catalog' => ['counts' => ['collection_items' => 1]],
                'event' => ['counts' => ['events' => 1]],
            ]));

        $service = $this->makeService($bff, $store);
        $service->read(42, ['users.read']);
        $this->removeFreshEntries($store);

        $busyLock = $this->createMock(DashboardLockInterface::class);
        $busyLock->expects($this->once())->method('acquire')->willReturn(null);
        $busyService = new DashboardDataService(
            $bff,
            $this->createMock(DomainApiClientInterface::class),
            $this->createMock(DomainApiClientInterface::class),
            $this->createMock(DomainApiClientInterface::class),
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

    /** @param array<string, mixed> $store */
    private function makeService(BffApiClientInterface $bff, array &$store): DashboardDataService
    {
        return new DashboardDataService(
            $bff,
            $this->createMock(DomainApiClientInterface::class),
            $this->createMock(DomainApiClientInterface::class),
            $this->createMock(DomainApiClientInterface::class),
            $this->makeCache($store),
            $this->lock(),
            300,
            3600,
            15,
            0,
        );
    }

    private function lock(): DashboardLockInterface
    {
        $lock = $this->createMock(DashboardLockInterface::class);
        $lock->method('acquire')->willReturn('lock-token');

        return $lock;
    }

    /**
     * @param array<string, mixed> $sections
     * @param array<string, string> $sourceOverrides
     * @return array<string, mixed>
     */
    private function bffResponse(
        array $sections,
        int $status = 200,
        bool $ok = true,
        array $sourceOverrides = [],
    ): array {
        $source = array_merge([
            'hub' => 'ok',
            'cms' => 'ok',
            'catalog' => 'ok',
            'event' => 'ok',
        ], $sourceOverrides);
        $source['state'] = count(array_filter(
            $source,
            static fn (string $state): bool => $state === 'ok'
        )) === 4 ? 'ok' : 'partial';

        return [
            'ok' => $ok,
            'status' => $status,
            'data' => $ok ? [
                'status' => 'success',
                'data' => [
                    'version' => 1,
                    'generated_at' => date(DATE_ATOM),
                    'source' => $source,
                    'sections' => $sections,
                ],
            ] : [],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }

    /** @param array<string, mixed> $store */
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
