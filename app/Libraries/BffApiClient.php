<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\BffApiClient as BffApiClientConfig;

/**
 * HTTP client targeting a ci4-bff-starter gateway.
 *
 * Reuses every behaviour of {@see ApiClient} (auth header injection,
 * app-key forwarding, upload handling) but defaults to the
 * `BffApiClient` config instead of `ApiClient`. Token refresh is
 * delegated to the Hub client — see {@see SecondaryApiClient} — because
 * the BFF never issues or refreshes JWTs itself (it only introspects them
 * against the Hub).
 */
class BffApiClient extends SecondaryApiClient implements BffApiClientInterface
{
    public function __construct(?BffApiClientConfig $config = null, ?ApiClientInterface $hubClient = null)
    {
        parent::__construct($config ?? config(BffApiClientConfig::class), $hubClient ?? service('apiClient'));
    }

    /**
     * Read the authenticated Admin dashboard aggregate from the BFF.
     *
     * The BFF owns the fan-out to Hub and the three domain applications. The
     * Admin still owns the outer cache and stale/cooldown policy.
     *
     * @return array<string, mixed>
     */
    public function getAdminDashboard(int $maxRetries = 2): array
    {
        return $this->request('GET', '/me/admin-dashboard', [
            'max_retries' => $maxRetries,
        ], true);
    }
}
