<?php

declare(strict_types=1);

namespace Config;

/**
 * Configuration for the HTTP client that monitors the public website
 * (ci4-website-builder-web, port 8186 by default).
 *
 * Leave $baseUrl empty (the default) to disable web health monitoring on
 * the dashboard.
 */
class WebApiClient extends ApiClient
{
    public string $baseUrl = '';

    public function __construct()
    {
        \CodeIgniter\Config\BaseConfig::__construct();

        $baseUrl = env('webApiClient.baseUrl') ?: env('WEB_BASE_URL');
        if (is_string($baseUrl) && trim($baseUrl) !== '') {
            $this->baseUrl = $baseUrl;
        }

        $timeout = env('webApiClient.timeout') ?: env('WEB_TIMEOUT');
        if ($timeout !== false && $timeout !== null && $timeout !== '') {
            $this->timeout = (int) $timeout;
        }

        $connectTimeout = env('webApiClient.connectTimeout') ?: env('WEB_CONNECT_TIMEOUT');
        if ($connectTimeout !== false && $connectTimeout !== null && $connectTimeout !== '') {
            $this->connectTimeout = (int) $connectTimeout;
        }

        $val = env('webApiClient.healthPaths') ?: env('WEB_HEALTH_PATHS');
        if ($val) {
            $paths = array_values(array_filter(array_map('trim', explode(',', (string) $val))));
            if ($paths !== []) {
                $this->healthPaths = $paths;
            }
        }
    }
}
