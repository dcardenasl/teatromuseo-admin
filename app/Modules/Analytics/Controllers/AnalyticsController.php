<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Controllers;

use App\Controllers\BaseWebController;
use App\Modules\Analytics\Services\AnalyticsApiService;
use App\Support\CatalogOptions;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AnalyticsController extends BaseWebController
{
    protected AnalyticsApiService $analyticsService;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->analyticsService = service('analyticsApiService');
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

        $params = ['period' => $period];

        $overviewResponse  = $this->safeApiCall(fn () => $this->analyticsService->overview($params));
        $pagesResponse     = $this->safeApiCall(fn () => $this->analyticsService->pages(array_merge($params, ['limit' => 10])));
        $referrersResponse = $this->safeApiCall(fn () => $this->analyticsService->referrers(array_merge($params, ['limit' => 10])));
        $devicesResponse   = $this->safeApiCall(fn () => $this->analyticsService->devices($params));
        $timeseriesResponse = $this->safeApiCall(fn () => $this->analyticsService->timeseries($params));

        $this->maybeFlashDevError($overviewResponse);
        $this->maybeFlashDevError($pagesResponse);
        $this->maybeFlashDevError($referrersResponse);
        $this->maybeFlashDevError($devicesResponse);
        $this->maybeFlashDevError($timeseriesResponse);

        $overview  = $this->extractData($overviewResponse);
        $pages     = $this->extractData($pagesResponse);
        $referrers = $this->extractData($referrersResponse);
        $devices   = $this->extractData($devicesResponse);
        $timeseries = $this->extractData($timeseriesResponse);

        $pagesData      = isset($pages['data']) && is_array($pages['data']) ? $pages['data'] : [];
        $referrersData  = isset($referrers['data']) && is_array($referrers['data']) ? $referrers['data'] : [];
        $timeseriesData = isset($timeseries['data']) && is_array($timeseries['data']) ? $timeseries['data'] : [];

        return $this->render('analytics/index', [
            'title'          => lang('Analytics.title'),
            'overview'       => $overview,
            'pages'          => $pagesData,
            'referrers'      => $referrersData,
            'devices'        => $devices,
            'timeseries'     => $timeseriesData,
            'filters'        => ['period' => $period],
            'defaultFilters' => $defaultFilters,
            'hasFilters'     => $period !== $defaultFilters['period'],
            'periodOptions'  => $periodOptions,
        ]);
    }
}
