<?php

declare(strict_types=1);

namespace App\Modules\Metrics\Services;

use App\Libraries\BffApiClientInterface;
use App\Support\BffAvailability;

/** Normalizes the Hub-owned Metrics workspace for the existing view. */
final class MetricsWorkspaceBffAdapter
{
    private bool $unavailable = false;

    public function __construct(private readonly BffApiClientInterface $bffClient)
    {
    }

    /** @return array{summary: array<string, mixed>, timeseries: list<array<string, mixed>>}|null */
    public function read(string $period): ?array
    {
        $response = $this->bffClient->getAdminMetricsWorkspace($period);
        $this->unavailable = BffAvailability::isUnavailable($response);
        if ($this->unavailable || ($response['ok'] ?? false) !== true) {

            return null;
        }

        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $summary = is_array($data['summary'] ?? null) ? $data['summary'] : [];
        $timeseries = is_array($data['timeseries'] ?? null) ? $data['timeseries'] : [];

        return [
            'summary' => $summary,
            'timeseries' => array_values(array_filter($timeseries, 'is_array')),
        ];
    }

    public function wasUnavailable(): bool
    {
        return $this->unavailable;
    }
}
