<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\DomainApiClient as DomainApiClientConfig;

/**
 * Secondary HTTP client targeting a domain-starter app.
 *
 * Reuses every behaviour of {@see ApiClient} (auth header injection,
 * app-key forwarding, upload handling) but defaults to the
 * `DomainApiClient` config instead of `ApiClient`. Token refresh is
 * delegated to the Hub client — see {@see SecondaryApiClient} — because
 * domain apps never issue or refresh JWTs themselves.
 */
class DomainApiClient extends SecondaryApiClient implements DomainApiClientInterface
{
    public function __construct(?DomainApiClientConfig $config = null, ?ApiClientInterface $hubClient = null)
    {
        parent::__construct($config ?? config(DomainApiClientConfig::class), $hubClient ?? service('apiClient'));
    }
}
