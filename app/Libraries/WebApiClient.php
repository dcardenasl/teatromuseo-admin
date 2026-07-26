<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\WebApiClient as WebApiClientConfig;

/**
 * HTTP client targeting the public website (ci4-website-builder-web).
 *
 * Reuses all behaviour of {@see ApiClient} but reads from the `WebApiClient`
 * config so the admin can monitor the public site's health independently.
 */
class WebApiClient extends ApiClient implements WebApiClientInterface
{
    public function __construct(?WebApiClientConfig $config = null)
    {
        parent::__construct($config ?? config(WebApiClientConfig::class));
    }
}
