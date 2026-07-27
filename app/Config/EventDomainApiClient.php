<?php

declare(strict_types=1);

namespace Config;

/**
 * HTTP client configuration for the Teatro Museo event domain.
 *
 * Event CRUD calls must use the event domain origin instead of the CMS origin.
 */
class EventDomainApiClient extends DomainApiClient
{
    public string $baseUrl = 'http://localhost:8193';

    public function __construct()
    {
        parent::__construct();

        $baseUrl = env('eventDomainApiClient.baseUrl') ?: env('EVENT_DOMAIN_API_BASE_URL') ?: $this->baseUrl;
        if (! is_string($baseUrl) || trim($baseUrl) === '') {
            throw new \LogicException(
                'Missing EVENT_DOMAIN_API_BASE_URL in .env. '
                . 'Set eventDomainApiClient.baseUrl or EVENT_DOMAIN_API_BASE_URL to the event domain URL. '
                . 'Example: EVENT_DOMAIN_API_BASE_URL=http://localhost:8193'
            );
        }

        $this->baseUrl = $baseUrl;

        $appKey = env('eventDomainApiClient.appKey') ?: env('EVENT_DOMAIN_API_KEY');
        if (is_string($appKey) && trim($appKey) !== '') {
            $this->appKey = $appKey;
        }

        $appName = env('eventDomainApiClient.appName') ?: env('EVENT_DOMAIN_API_APP_NAME');
        if (is_string($appName) && trim($appName) !== '') {
            $this->appName = $appName;
        }
    }
}
