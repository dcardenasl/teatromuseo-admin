<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\ApiClient as ApiClientConfig;

/**
 * Base for HTTP clients that talk to a backend other than the Hub (a domain
 * app, the BFF). Only the Hub issues and refreshes JWTs — domain apps and
 * the BFF delegate auth entirely to it and never expose `/auth/refresh`
 * themselves — so token refresh must always be delegated to a Hub
 * {@see ApiClientInterface} instance instead of the inherited
 * {@see ApiClient::attemptTokenRefresh()}, which would otherwise call that
 * missing endpoint on the secondary backend's own host.
 *
 * Both clients share the same PHP session as the Hub client, so a refresh
 * performed here is immediately visible to every client on the request.
 */
abstract class SecondaryApiClient extends ApiClient
{
    public function __construct(
        ApiClientConfig $config,
        private readonly ApiClientInterface $hubClient,
    ) {
        parent::__construct($config);
    }

    public function attemptTokenRefresh(): bool
    {
        return $this->hubClient->attemptTokenRefresh();
    }
}
