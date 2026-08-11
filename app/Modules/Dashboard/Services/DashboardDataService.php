<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClientInterface;
use CodeIgniter\Cache\CacheInterface;

/**
 * Bounded, permission-aware dashboard delivery.
 *
 * The service hides transport, cache, stale and single-flight behaviour behind
 * one seam. The controller and views only consume the normalized sections.
 */
final readonly class DashboardDataService
{
    private const CACHE_VERSION = 2;

    public function __construct(
        private ApiClientInterface $hubClient,
        private DomainApiClientInterface $cmsClient,
        private DomainApiClientInterface $catalogClient,
        private DomainApiClientInterface $eventClient,
        private CacheInterface $cache,
        private DashboardLockInterface $lock,
        private int $freshTtl,
        private int $staleTtl,
        private int $failureCooldownTtl,
        private int $maxRetries,
    ) {
    }

    /**
     * @param list<string> $permissions
     * @return array<string, mixed>
     */
    public function read(int $userId, array $permissions): array
    {
        $permissionScope = $permissions;
        sort($permissionScope);
        $key = 'dashboard_data_v' . self::CACHE_VERSION . '_' . $userId . '_' . hash('sha256', implode('|', $permissionScope));
        $freshKey = $key . '_fresh';
        $staleKey = $key . '_stale';
        $cooldownKey = $key . '_failure_cooldown';

        $fresh = $this->cache->get($freshKey);
        if (is_array($fresh)) {
            return $this->withSourceState($fresh, 'fresh');
        }

        $cooldown = $this->cache->get($cooldownKey);
        if (is_array($cooldown)) {
            $source = is_array($cooldown['source'] ?? null) ? $cooldown['source'] : [];
            $state = is_string($source['state'] ?? null) ? $source['state'] : 'unavailable';

            return $this->withSourceState($cooldown, $state, 'failure_cooldown');
        }

        $token = $this->lock->acquire($key);
        if ($token === null) {
            $stale = $this->cache->get($staleKey);
            if (is_array($stale)) {
                return $this->withSourceState($stale, 'stale', 'builder_busy');
            }

            return $this->unavailable('builder_busy');
        }

        try {
            $fresh = $this->cache->get($freshKey);
            if (is_array($fresh)) {
                return $this->withSourceState($fresh, 'fresh');
            }

            $hub = $this->readUpstream(
                $this->hubClient,
                '/admin/dashboard/summary',
                $key . '_hub'
            );
            $cms = $this->readUpstream(
                $this->cmsClient,
                '/cms/dashboard/summary',
                $key . '_cms'
            );
            $catalog = $this->readUpstream(
                $this->catalogClient,
                '/catalog/dashboard/summary',
                $key . '_catalog'
            );
            $event = $this->readUpstream(
                $this->eventClient,
                '/events/dashboard/summary',
                $key . '_event'
            );

            $snapshot = [
                'version' => self::CACHE_VERSION,
                'generated_at' => date(DATE_ATOM),
                'source' => [
                    'hub' => $hub['state'],
                    'cms' => $cms['state'],
                    'catalog' => $catalog['state'],
                    'event' => $event['state'],
                    'state' => $this->overallState([
                        $hub['state'],
                        $cms['state'],
                        $catalog['state'],
                        $event['state'],
                    ]),
                ],
                'sections' => [
                    'hub' => $hub['sections'],
                    'cms' => $cms['sections'],
                    'catalog' => $catalog['sections'],
                    'event' => $event['sections'],
                ],
            ];

            if ($hub['state'] === 'fresh'
                && $cms['state'] === 'fresh'
                && $catalog['state'] === 'fresh'
                && $event['state'] === 'fresh') {
                $this->cache->save($freshKey, $snapshot, $this->freshTtl);
                $this->cache->save($staleKey, $snapshot, $this->staleTtl);
            } elseif ($this->failureCooldownTtl > 0) {
                // A cold outage must not make every sequential widget retry
                // the same upstreams. Keep the partial/unavailable result for
                // a short cooldown; the next request after it expires is the
                // single revalidation attempt.
                $this->cache->save($cooldownKey, $snapshot, $this->failureCooldownTtl);
            }

            return $snapshot;
        } finally {
            $this->lock->release($key, $token);
        }
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function withSourceState(array $snapshot, string $state, ?string $reason = null): array
    {
        $source = is_array($snapshot['source'] ?? null) ? $snapshot['source'] : [];
        $source['state'] = $state;
        if ($reason !== null) {
            $source['reason'] = $reason;
        }
        $snapshot['source'] = $source;

        return $snapshot;
    }

    /**
     * @return array{state: string, sections: array<string, mixed>}
     */
    private function readUpstream(ApiClientInterface $client, string $path, string $key): array
    {
        $freshKey = $key . '_fresh';
        $staleKey = $key . '_stale';
        $cached = $this->cache->get($freshKey);
        if (is_array($cached)) {
            return ['state' => 'fresh', 'sections' => $this->sections($cached)];
        }

        $response = $client->request('GET', $path, [
            'max_retries' => $this->maxRetries,
        ], true);
        if (($response['ok'] ?? false) === true) {
            $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
            $sections = $this->sections($payload);
            $this->cache->save($freshKey, $payload, $this->freshTtl);
            $this->cache->save($staleKey, $payload, $this->staleTtl);

            return ['state' => 'fresh', 'sections' => $sections];
        }

        $status = (int) ($response['status'] ?? 0);
        $stale = $this->cache->get($staleKey);
        if (($status === 0 || $status >= 500) && is_array($stale)) {
            return ['state' => 'stale', 'sections' => $this->sections($stale)];
        }

        return ['state' => 'unavailable', 'sections' => []];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sections(array $payload): array
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $sections = $data['sections'] ?? [];

        return is_array($sections) ? $sections : [];
    }

    /** @param list<string> $states */
    private function overallState(array $states): string
    {
        if ($states !== [] && count(array_unique($states)) === 1 && $states[0] === 'fresh') {
            return 'fresh';
        }

        if (in_array('fresh', $states, true) || in_array('stale', $states, true)) {
            return 'stale';
        }

        return 'unavailable';
    }

    /** @return array<string, mixed> */
    private function unavailable(string $reason): array
    {
        return [
            'version' => self::CACHE_VERSION,
            'generated_at' => date(DATE_ATOM),
            'source' => ['state' => 'unavailable', 'reason' => $reason],
            'sections' => ['hub' => [], 'cms' => [], 'catalog' => [], 'event' => []],
        ];
    }
}
