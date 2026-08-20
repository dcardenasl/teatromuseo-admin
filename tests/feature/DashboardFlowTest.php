<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
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
        $this->assertStringContainsString(lang('Dashboard.system_status'), $result->getBody());
        $this->assertStringNotContainsString('dashboard/widgets/', $result->getBody());
    }

    public function testDashboardAggregatesAdminMetrics(): void
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
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('42', $body);
        $this->assertStringContainsString('99.9%', $body);
    }

    public function testDashboardStillRendersWhenUserSummaryFails(): void
    {
        $this->injectDashboardSummary(hubOk: false);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['users.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
    }

    public function testDashboardProjectsSourceHealthFromAggregate(): void
    {
        $this->injectDashboardSummary(hubOk: false);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $this->assertStringContainsString(lang('Dashboard.status_down'), $result->getBody());
    }

    public function testDashboardRendersRecentFiles(): void
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
        ])->get('/dashboard');

        $result->assertStatus(200);
        $this->assertStringContainsString('report.pdf', $result->getBody());
    }

    public function testDashboardPrefixesHubBaseUrlForRelativeImageVariants(): void
    {
        $relativeVariant = '/uploads/2026/01/01/example_sm.webp';
        $this->injectDashboardSummary(
            hubSections: [
                'files' => [
                    'total' => 1,
                    'recent' => [[
                        'id' => 100,
                        'original_name' => 'example.webp',
                        'category' => 'image',
                        'human_size' => '1 MB',
                        'uploaded_at' => '2026-01-01 00:00:00',
                        'is_image' => true,
                        'variants' => ['sm' => ['url' => $relativeVariant]],
                    ]],
                ],
            ],
        );

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $expectedUrl = rtrim((string) config('ApiClient')->baseUrl, '/') . $relativeVariant;
        $this->assertStringContainsString($expectedUrl, $body);
        $this->assertStringNotContainsString('src="' . $relativeVariant . '"', $body);
    }

    public function testDashboardRendersLanguageBarsWhenPermitted(): void
    {
        $this->injectDashboardSummary(
            translationsSections: [
                'translations' => [['code' => 'fr', 'name' => 'French', 'percentage' => 1, 'is_default' => false]],
            ],
        );

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.languages.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('FR', $body);
        $this->assertStringContainsString('French', $body);
        $this->assertStringContainsString('1%', $body);
    }

    public function testDashboardRendersWithoutTranslationPermission(): void
    {
        $this->injectDashboardSummary();

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard');

        $result->assertStatus(200);
    }

    public function testDashboardRendersTrafficOverviewWhenPermitted(): void
    {
        $this->injectDashboardSummary(
            analyticsSections: [
                'analytics' => [
                    'total_views'     => 1234,
                    'unique_visitors' => 567,
                    'top_page'        => '/inicio',
                    'top_page_title'  => 'Inicio',
                    'top_referrer'    => 'google.com',
                    'period'          => '7d',
                ],
            ],
        );

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.analytics.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('1,234', $body);
        $this->assertStringContainsString('567', $body);
        $this->assertStringContainsString('Inicio', $body);
        $this->assertStringContainsString('google.com', $body);
    }

    public function testDashboardRendersWithoutAnalyticsPermission(): void
    {
        $this->injectDashboardSummary();

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => []],
        ])->get('/dashboard');

        $result->assertStatus(200);
    }

    public function testDashboardSummaryOnlyQueriesPermittedResources(): void
    {
        $this->injectDashboardSummary(cmsSections: ['counts' => ['pages' => 7]]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.pages.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('admin/cms/pages', $body);
        $this->assertStringContainsString('>7<', $body);
    }

    public function testDashboardSummaryCountsFormsFromTheUnpaginatedListResponse(): void
    {
        $this->injectDashboardSummary(cmsSections: ['counts' => ['forms' => 2]]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.forms.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertStringContainsString('admin/cms/forms', $body);
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*2\s*</', $body);
    }

    public function testDashboardSummaryShowsSubmissionsTotalAndPendingBadgeWhenPermitted(): void
    {
        $this->injectDashboardSummary(cmsSections: [
            'submissions' => ['new' => 3, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.submissions.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        // Card shows the total across all statuses (3+5+2+0+1 = 11)...
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*11\s*</', $body);
        // ...and a separate badge for the ones still needing a reply (new = 3).
        $this->assertMatchesRegularExpression('/<span class="[^"]*bg-red-500[^"]*"[^>]*>\s*3\s*<\/span>/', $body);
        $this->assertStringContainsString('admin/cms/form-submissions?status=new', $body);
    }

    public function testDashboardSummaryOmitsSubmissionsBadgeWhenNothingIsPending(): void
    {
        $this->injectDashboardSummary(cmsSections: [
            'submissions' => ['new' => 0, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['id' => 1, 'first_name' => 'Admin', 'permissions' => ['cms.submissions.read']],
        ])->get('/dashboard');

        $result->assertStatus(200);
        $body = $result->getBody();
        $this->assertMatchesRegularExpression('/text-xl font-bold text-gray-900">\s*8\s*</', $body);
        $this->assertStringNotContainsString('bg-red-500', $body);
    }

    public function testDashboardCmsActivityMergesPagesAndEntriesSortedByRecency(): void
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
        ])->get('/dashboard');

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
     * @param array<string, mixed> $catalogSections
     * @param array<string, mixed> $eventSections
     * @param array<string, mixed> $analyticsSections
     * @param array<string, mixed> $translationsSections
     */
    private function injectDashboardSummary(
        array $hubSections = [],
        array $cmsSections = [],
        bool $hubOk = true,
        bool $cmsOk = true,
        array $catalogSections = [],
        array $eventSections = [],
        array $analyticsSections = [],
        array $translationsSections = [],
    ): void {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminDashboard')
            ->willReturn($this->aggregateResponse(
                [
                    'hub' => $hubSections,
                    'cms' => $cmsSections,
                    'analytics' => $analyticsSections,
                    'translations' => $translationsSections,
                    'catalog' => $catalogSections,
                    'event' => $eventSections,
                ],
                $hubOk,
                $cmsOk,
            ));
        Services::injectMock('bffApiClient', $bff);
    }

    /**
     * @param array<string, array<string, mixed>> $sections
     */
    private function aggregateResponse(array $sections, bool $hubOk, bool $cmsOk): array
    {
        $source = [
            'hub' => $hubOk ? 'ok' : 'unavailable',
            'cms' => $cmsOk ? 'ok' : 'unavailable',
            'analytics' => 'ok',
            'translations' => 'ok',
            'catalog' => 'ok',
            'event' => 'ok',
        ];
        $source['state'] = in_array('unavailable', $source, true) ? 'partial' : 'ok';

        return [
            'ok'          => true,
            'status'      => 200,
            'data'        => [
                'status' => 'success',
                'data' => [
                    'version' => 1,
                    'generated_at' => date(DATE_ATOM),
                    'source' => $source,
                    'sections' => $sections,
                ],
            ],
            'raw'         => '',
            'headers'     => [],
            'messages'    => [],
            'fieldErrors' => [],
        ];
    }
}
