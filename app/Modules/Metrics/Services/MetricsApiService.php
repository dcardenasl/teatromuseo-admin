<?php

declare(strict_types=1);

namespace App\Modules\Metrics\Services;

use App\Services\BaseApiService;

/**
 * @phpstan-import-type ApiResponse from \App\Libraries\ApiClientInterface
 */
class MetricsApiService extends BaseApiService
{
    /**
     * @param array<string, mixed> $filters
     * @return ApiResponse
     */
    public function summary(array $filters = []): array
    {
        return $this->apiClient->get('/metrics', $filters);
    }

    /**
     * @return array{ok: bool, status: int, data: array<string, mixed>|list<array<string, mixed>>, raw: string, headers: array<string, string>, messages: list<string>, fieldErrors: array<string, string>}
     * @param array<string, mixed> $filters
     */
    public function timeseries(array $filters = []): array
    {
        $response = $this->apiClient->get('/metrics/timeseries', $filters);

        if (! ($response['ok'] ?? false)) {
            return $response;
        }

        // ApiClient::request() stores the full decoded response envelope
        // ({status, data, ...}) under $response['data'], so the actual
        // payload is one level deeper — same shape extractData() unwraps
        // for every other endpoint. Transform the parallel arrays (dates,
        // requests, etc.) into a list of point objects at that inner level.
        $envelope = $response['data'] ?? [];
        $data = is_array($envelope) ? ($envelope['data'] ?? []) : [];
        if (is_array($data) && isset($data['dates']) && is_array($data['dates'])) {
            $points = [];
            foreach ($data['dates'] as $i => $date) {
                $points[] = [
                    'period'  => $date,
                    'value'   => $data['requests'][$i] ?? 0,
                    'errors'  => $data['errors'][$i] ?? 0,
                    'latency' => $data['latency'][$i] ?? 0,
                ];
            }
            $envelope['data'] = $points;
            $response['data'] = $envelope;
        }

        return $response;
    }
}
