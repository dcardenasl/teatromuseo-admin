<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Services;

use App\Libraries\BffApiClientInterface;
use CodeIgniter\Cache\CacheInterface;

/**
 * Bounded, permission-aware dashboard delivery.
 *
 * The service hides transport, cache, stale and single-flight behaviour behind
 * one seam. The controller and views only consume the normalized sections.
 */
final readonly class DashboardDataService
{
    private const CACHE_VERSION = 3;

    public function __construct(
        private BffApiClientInterface $bffClient,
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

            $response = $this->bffClient->getAdminDashboard($this->maxRetries);
            $snapshot = $this->snapshotFromBff($response);

            if ($snapshot === null) {
                $status = (int) ($response['status'] ?? 0);
                $stale = $this->cache->get($staleKey);
                if (($status === 0 || $status >= 500) && is_array($stale)) {
                    $snapshot = $this->withStaleSourceState($stale, 'bff_unavailable');
                    if ($this->failureCooldownTtl > 0) {
                        $this->cache->save($cooldownKey, $snapshot, $this->failureCooldownTtl);
                    }

                    return $snapshot;
                }

                $snapshot = $this->unavailable('bff_unavailable');
            }

            if (($snapshot['source']['state'] ?? null) === 'fresh') {
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
     * Translate the BFF's source contract to the Admin's existing freshness
     * contract without leaking the BFF's transport-specific `ok` state.
     *
     * @param array<string, mixed> $response
     * @return array<string, mixed>|null
     */
    private function snapshotFromBff(array $response): ?array
    {
        if (($response['ok'] ?? false) !== true) {
            return null;
        }

        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $source = is_array($data['source'] ?? null) ? $data['source'] : [];
        $sections = is_array($data['sections'] ?? null) ? $data['sections'] : [];
        $states = [];
        $mappedSource = [];
        $mappedSections = [];

        foreach (['hub', 'cms', 'analytics', 'translations', 'catalog', 'event'] as $key) {
            if (! array_key_exists($key, $source) && in_array($key, ['analytics', 'translations'], true)) {
                continue;
            }
            $state = ($source[$key] ?? null) === 'ok' ? 'fresh' : 'unavailable';
            $mappedSource[$key] = $state;
            $mappedSections[$key] = is_array($sections[$key] ?? null)
                ? $sections[$key]
                : [];
            $states[] = $state;
        }

        $mappedSource['state'] = $this->overallState($states);

        return [
            'version' => self::CACHE_VERSION,
            'generated_at' => is_string($data['generated_at'] ?? null)
                ? $data['generated_at']
                : date(DATE_ATOM),
            'source' => $mappedSource,
            'sections' => $mappedSections,
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return array<string, mixed>
     */
    private function withStaleSourceState(array $snapshot, string $reason): array
    {
        $source = is_array($snapshot['source'] ?? null) ? $snapshot['source'] : [];
        foreach (['hub', 'cms', 'analytics', 'translations', 'catalog', 'event'] as $key) {
            if (! array_key_exists($key, $source) && in_array($key, ['analytics', 'translations'], true)) {
                continue;
            }
            $source[$key] = 'stale';
        }
        $source['state'] = 'stale';
        $source['reason'] = $reason;
        $snapshot['source'] = $source;

        return $snapshot;
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
            'source' => [
                'hub' => 'unavailable',
                'cms' => 'unavailable',
                'catalog' => 'unavailable',
                'event' => 'unavailable',
                'analytics' => 'unavailable',
                'translations' => 'unavailable',
                'state' => 'unavailable',
                'reason' => $reason,
            ],
            'sections' => [
                'hub' => [],
                'cms' => [],
                'analytics' => [],
                'translations' => [],
                'catalog' => [],
                'event' => [],
            ],
        ];
    }
}
