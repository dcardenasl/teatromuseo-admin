<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Marker interface for the HTTP client targeting the public website.
 *
 * Separate symbol allows the DI container in {@see \Config\Services} to
 * distinguish the web client from hub, domain, and BFF clients.
 */
interface WebApiClientInterface extends ApiClientInterface
{
}
