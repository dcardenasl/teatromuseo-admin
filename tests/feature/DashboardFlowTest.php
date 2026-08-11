<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClientInterface;
use App\Modules\Analytics\Services\AnalyticsApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Dashboard\Services\HealthApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class DashboardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
    }

    protected function tearDown(): void
    {
        cache()->clean();
        Services::reset();
        parent::tearDown();
    }

    public function testDashboardIndexReturnsPageShell(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['users.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $this->assertStringContainsString(lang('Dashboard.title'), $result->getBody());
        $this->assertStringContainsString('dashboard/widgets/stats', $result->getBody());
        $this->assertStringContainsString('dashboard/widgets/health', $result->getBody());
    }

    public function testWidgetStatsAggregatesAdminMetrics(): void
    {
        $this->injectDashboardSummary(
            hubSections: [
                'users' => ['total' => 42],
                'files' => ['total' => 5, 'recent' => []],
                'metrics' => ['request_stats' => ['availability_percent' => 99.9]],
            ],
        );

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['users.read', 'metrics.read']],
        ])->get('/dashboard/widgets/stats');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('42', $body);
        $this->assertStringContainsString('99.9%', $body);
    }

    public function testWidgetStatsStillRendersWhenUserSummaryFails(): void
    {
        $this->injectDashboardSummary(hubStatus: 500, hubOk: false);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['users.read']],
        ])->get('/dashboard/widgets/stats');

        $result->assertStatus(200);
    }

    public function testWidgetHealthReturnsHealthCard(): void
    {
        $healthService = $this->createMock(HealthApiService::class);
        $healthService->expects($this->once())
            ->method('check')
            ->willReturn([
                'ok'         => true,
                'state'      => 'up',
                'status'     => 200,
                'path'       => '/health',
                'latency_ms' => 42,
                'data'       => ['state' => 'up'],
                'raw'        => '',
                'headers'    => [],
                'messages'   => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('healthApiService', $healthService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard/widgets/health');

        $result->assertStatus(200);
        $this->assertStringContainsString('42', $result->getBody());
    }

    public function testWidgetRecentFilesReturnsFileList(): void
    {
        $this->injectDashboardSummary(
            hubSections: [
                'files' => [
                    'total' => 1,
                    'recent' => [[
                        'id' => 99,
                        'original_name' => 'report.pdf',
                        'category' => 'document',
                        'human_size' => '1 MB',
                        'uploaded_at' => '2026-01-01 00:00:00',
                        'is_image' => false,
                    ]],
                ],
            ],
        );

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard/widgets/recent-files');

        $result->assertStatus(200);
        $this->assertStringContainsString('report.pdf', $result->getBody());
    }

    public function testWidgetTranslationsRendersLanguageBarsWhenPermitted(): void
    {
        $translationService = $this->createMock(TranslationAuditApiService::class);
        $translationService->expects($this->once())
            ->method('getStats')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => [['code' => 'fr', 'name' => 'French', 'percentage' => 1, 'is_default' => false]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('translationAuditApiService', $translationService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.languages.read']],
        ])->get('/dashboard/widgets/translations');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('FR', $body);
        $this->assertStringContainsString('French', $body);
        $this->assertStringContainsString('1%', $body);
    }

    public function testWidgetTranslationsSkipsTheApiCallWithoutPermission(): void
    {
        $translationService = $this->createMock(TranslationAuditApiService::class);
        $translationService->expects($this->never())->method('getStats');
        Services::injectMock('translationAuditApiService', $translationService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard/widgets/translations');

        $result->assertStatus(200);
    }

    public function testWidgetAnalyticsRendersTrafficOverviewWhenPermitted(): void
    {
        $analyticsService = $this->createMock(AnalyticsApiService::class);
        $analyticsService->expects($this->once())
            ->method('overview')
            ->with(['period' => '7d'])
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => [
                    'total_views'     => 1234,
                    'unique_visitors' => 567,
                    'top_page'        => '/inicio',
                    'top_page_title'  => 'Inicio',
                    'top_referrer'    => 'google.com',
                    'period'          => '7d',
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('analyticsApiService', $analyticsService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.analytics.read']],
        ])->get('/dashboard/widgets/analytics');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('1,234', $body);
        $this->assertStringContainsString('567', $body);
        $this->assertStringContainsString('Inicio', $body);
        $this->assertStringContainsString('google.com', $body);
    }

    public function testWidgetAnalyticsSkipsTheApiCallWithoutPermission(): void
    {
        $analyticsService = $this->createMock(AnalyticsApiService::class);
        $analyticsService->expects($this->never())->method('overview');
        Services::injectMock('analyticsApiService', $analyticsService);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard/widgets/analytics');

        $result->assertStatus(200);
    }

    public function testWidgetSummaryOnlyQueriesPermittedResources(): void
    {
        $this->injectDashboardSummary(cmsSections: ['counts' => ['pages' => 7]]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.pages.read']],
        ])->get('/dashboard/widgets/summary');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('admin/cms/pages', $body);
        $this->assertStringContainsString('>7<', $body);
    }

    public function testWidgetSummaryCountsFormsFromTheUnpaginatedListResponse(): void
    {
        $this->injectDashboardSummary(cmsSections: ['counts' => ['forms' => 2]]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.forms.read']],
        ])->get('/dashboard/widgets/summary');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('admin/cms/forms', $body);
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*2\s*</', $body);
    }

    public function testWidgetSummaryShowsSubmissionsTotalAndPendingBadgeWhenPermitted(): void
    {
        $this->injectDashboardSummary(cmsSections: [
            'submissions' => ['new' => 3, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.submissions.read']],
        ])->get('/dashboard/widgets/summary');

        $result->assertStatus(200);
        $body = $result->getBody();
        // Card shows the total across all statuses (3+5+2+0+1 = 11)...
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*11\s*</', $body);
        // ...and a separate badge for the ones still needing a reply (new = 3).
        $this->assertMatchesRegularExpression('/<span class="[^"]*bg-red-500[^"]*"[^>]*>\s*3\s*<\/span>/', $body);
        $this->assertStringContainsString('admin/cms/form-submissions?status=new', $body);
    }

    public function testWidgetSummaryOmitsSubmissionsBadgeWhenNothingIsPending(): void
    {
        $this->injectDashboardSummary(cmsSections: [
            'submissions' => ['new' => 0, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.submissions.read']],
        ])->get('/dashboard/widgets/summary');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*8\s*</', $body);
        $this->assertStringNotContainsString('bg-red-500', $body);
    }

    public function testWidgetCmsActivityMergesPagesAndEntriesSortedByRecency(): void
    {
        $this->injectDashboardSummary(cmsSections: [
            'recent_activity' => [
                ['type' => 'page', 'id' => 1, 'updated_at' => '2026-07-01 00:00:00', 'translations' => [['title' => 'Old Home Page']]],
                ['type' => 'entry', 'id' => 5, 'updated_at' => '2026-07-20 12:00:00', 'translations' => [['title' => 'Recent News']]],
            ],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.pages.read', 'cms.entries.read']],
        ])->get('/dashboard/widgets/cms-activity');

        $result->assertStatus(200);
        $body = $result->getBody();
        $recentPos = strpos($body, 'Recent News');
        $oldPos    = strpos($body, 'Old Home Page');
        $this->assertNotFalse($recentPos);
        $this->assertNotFalse($oldPos);
        $this->assertLessThan($oldPos, $recentPos, 'The more recently updated entry should be listed first.');
    }

    /**
     * @param array<string, mixed> $hubSections
     * @param array<string, mixed> $cmsSections
     */
    private function injectDashboardSummary(
        array $hubSections = [],
        array $cmsSections = [],
        int $hubStatus = 200,
        bool $hubOk = true,
        int $cmsStatus = 200,
        bool $cmsOk = true,
    ): void {
        $hub = $this->createMock(ApiClientInterface::class);
        $hub->method('request')->willReturn($this->aggregateResponse($hubSections, $hubStatus, $hubOk));
        Services::injectMock('apiClient', $hub);

        $cms = $this->createMock(DomainApiClientInterface::class);
        $cms->method('request')->willReturn($this->aggregateResponse($cmsSections, $cmsStatus, $cmsOk));
        Services::injectMock('domainApiClient', $cms);
    }

    /** @param array<string, mixed> $sections */
    private function aggregateResponse(array $sections, int $status, bool $ok): array
    {
        return [
            'ok'          => $ok,
            'status'      => $status,
            'data'        => ['sections' => $sections],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }
}
