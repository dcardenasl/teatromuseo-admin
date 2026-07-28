<?php

declare(strict_types=1);

namespace Config;

/**
 * HTTP client configuration for the Teatro Museo catalog domain.
 *
 * Catalog CRUD calls must use the catalog domain origin instead of the CMS origin.
 */
class CatalogDomainApiClient extends DomainApiClient
{
    public string $baseUrl = 'http://localhost:8191';

    public function __construct()
    {
        $defaultBaseUrl = $this->baseUrl;
        parent::__construct();

        $baseUrl = env('catalogDomainApiClient.baseUrl') ?: env('CATALOG_DOMAIN_API_BASE_URL') ?: $defaultBaseUrl;
        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw new \LogicException(
                'Missing CATALOG_DOMAIN_API_BASE_URL in .env. '
                . 'Set catalogDomainApiClient.baseUrl or CATALOG_DOMAIN_API_BASE_URL to the catalog domain URL. '
                . 'Example: CATALOG_DOMAIN_API_BASE_URL=http://localhost:8191'
            );
        }

        $this->baseUrl = $baseUrl;

        $appKey = env('catalogDomainApiClient.appKey') ?: env('CATALOG_DOMAIN_API_KEY');
        if (is_string($appKey) && trim($appKey) !== '') {
            $this->appKey = $appKey;
        }

        $appName = env('catalogDomainApiClient.appName') ?: env('CATALOG_DOMAIN_API_APP_NAME');
        if (is_string($appName) && trim($appName) !== '') {
            $this->appName = $appName;
        }
    }
}
