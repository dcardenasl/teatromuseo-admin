<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Controllers;

use App\Controllers\BaseWebController;
use App\Libraries\BffApiClientInterface;
use App\Support\CatalogOptions;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AnalyticsController extends BaseWebController
{
    protected BffApiClientInterface $bffApiClient;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->bffApiClient = service('bffApiClient');
    }

    public function index(): string
    {
        $periodOptions = CatalogOptions::options([], 'analytics.periods', [
            ['value' => '1h',  'label' => lang('Analytics.period_1h')],
            ['value' => '24h', 'label' => lang('Analytics.period_24h')],
            ['value' => '7d',  'label' => lang('Analytics.period_7d')],
            ['value' => '30d', 'label' => lang('Analytics.period_30d')],
        ]);

        $defaultFilters = ['period' => '7d'];
        $rawPeriod      = $this->request->getGet('period');
        $period         = trim(is_string($rawPeriod) ? $rawPeriod : '7d');
        $allowed        = array_column($periodOptions, 'value');

        if (! in_array($period, $allowed, true)) {
            $period = '7d';
        }

        $response = $this->safeApiCall(fn () => $this->bffApiClient->getAdminAnalytics($period));
        $this->maybeFlashDevError($response);

        $analytics = $this->extractAnalyticsProjection($response);

        return $this->render('analytics/index', [
            'title'          => lang('Analytics.title'),
            'overview'       => $analytics['overview'],
            'pages'          => $analytics['pages'],
            'referrers'      => $analytics['referrers'],
            'devices'        => $analytics['devices'],
            'timeseries'     => $analytics['timeseries'],
            'filters'        => ['period' => $period],
            'defaultFilters' => $defaultFilters,
            'hasFilters'     => $period !== $defaultFilters['period'],
            'periodOptions'  => $periodOptions,
        ]);
    }

    /**
     * Translate the versioned BFF aggregate into the flat shape the existing
     * Analytics view consumes. A failed aggregate becomes empty sections,
     * never fabricated zero values.
     *
     * @param array<string, mixed> $response
     * @return array{overview: array<string, mixed>, pages: list<array<string, mixed>>, referrers: list<array<string, mixed>>, devices: array<string, mixed>, timeseries: list<array<string, mixed>>}
     */
    private function extractAnalyticsProjection(array $response): array
    {
        $aggregate = $this->extractData($response);
        $sections  = is_array($aggregate['sections'] ?? null) ? $aggregate['sections'] : [];

        $pages = $this->sectionRows($sections['pages']['data'] ?? null);
        $referrers = $this->sectionRows($sections['referrers']['data'] ?? null);
        $timeseries = $this->sectionRows($sections['timeseries']['data'] ?? null);

        return [
            'overview'  => is_array($sections['overview'] ?? null) ? $sections['overview'] : [],
            'pages'     => $pages,
            'referrers' => $referrers,
            'devices'   => is_array($sections['devices'] ?? null) ? $sections['devices'] : [],
            'timeseries' => $timeseries,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function sectionRows(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rows = [];
        foreach ($value as $row) {
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
