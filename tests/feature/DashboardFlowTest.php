<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Analytics\Services\AnalyticsApiService;
use App\Modules\Cms\Services\CategoryApiService;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\FormSubmissionApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Services\PageApiService;
use App\Modules\Cms\Services\TagApiService;
use App\Modules\Cms\Services\TranslationAuditApiService;
use App\Modules\Dashboard\Services\HealthApiService;
use App\Modules\Files\Services\FileApiService;
use App\Modules\Metrics\Services\MetricsApiService;
use App\Modules\Users\Services\UserApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class DashboardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
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
        $userService = $this->createMock(UserApiService::class);
        $userService->expects($this->once())
            ->method('list')
            ->with(['limit' => 1])
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => ['meta' => ['total' => 42]],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        $fileService = $this->createMock(FileApiService::class);
        $fileService->expects($this->once())
            ->method('list')
            ->with(['limit' => 5])
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => ['meta' => ['total' => 5], 'data' => []],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        $metricsService = $this->createMock(MetricsApiService::class);
        $metricsService->expects($this->once())
            ->method('summary')
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => ['request_stats' => ['availability_percent' => 99.9]],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('userApiService', $userService);
        Services::injectMock('fileApiService', $fileService);
        Services::injectMock('metricsApiService', $metricsService);

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
        $userService = $this->createMock(UserApiService::class);
        $userService->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok'          => false,
                'status'      => 500,
                'data'        => [],
                'raw'         => '',
                'headers'     => [],
                'messages'    => ['failed'],
                'fieldErrors' => [],
            ]);

        $fileService = $this->createMock(FileApiService::class);
        $fileService->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => ['meta' => ['total' => 0], 'data' => []],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        $metricsService = $this->createMock(MetricsApiService::class);
        $metricsService->expects($this->once())
            ->method('summary')
            ->willReturn([
                'ok'          => true,
                'status'      => 200,
                'data'        => [],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('userApiService', $userService);
        Services::injectMock('fileApiService', $fileService);
        Services::injectMock('metricsApiService', $metricsService);

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
        $fileService = $this->createMock(FileApiService::class);
        $fileService->expects($this->once())
            ->method('list')
            ->with(['limit' => 5])
            ->willReturn([
                'ok'     => true,
                'status' => 200,
                'data'   => [
                    'meta' => ['total' => 1],
                    'data' => [
                        ['id' => 99, 'original_name' => 'report.pdf', 'category' => 'document', 'human_size' => '1 MB', 'uploaded_at' => '2026-01-01 00:00:00', 'is_image' => false],
                    ],
                ],
                'raw'         => '',
                'headers'     => [],
                'messages'    => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('fileApiService', $fileService);

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
        $pageService = $this->createMock(PageApiService::class);
        $pageService->expects($this->once())
            ->method('list')
            ->with(['limit' => 1])
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['meta' => ['total' => 7]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageService);

        $entryService = $this->createMock(EntryApiService::class);
        $entryService->expects($this->never())->method('list');
        Services::injectMock('entryApiService', $entryService);
        $collectionService = $this->createMock(CollectionApiService::class);
        $collectionService->expects($this->never())->method('list');
        Services::injectMock('collectionApiService', $collectionService);
        $menuService = $this->createMock(MenuApiService::class);
        $menuService->expects($this->never())->method('list');
        Services::injectMock('menuApiService', $menuService);
        $categoryService = $this->createMock(CategoryApiService::class);
        $categoryService->expects($this->never())->method('list');
        Services::injectMock('categoryApiService', $categoryService);
        $tagService = $this->createMock(TagApiService::class);
        $tagService->expects($this->never())->method('list');
        Services::injectMock('tagApiService', $tagService);
        $formService = $this->createMock(FormApiService::class);
        $formService->expects($this->never())->method('list');
        Services::injectMock('formApiService', $formService);
        $submissionService = $this->createMock(FormSubmissionApiService::class);
        $submissionService->expects($this->never())->method('counts');
        Services::injectMock('formSubmissionApiService', $submissionService);

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
        // Unlike Pages/Entries/etc, the domain's /cms/forms endpoint ignores
        // filters and always returns a flat array with no `meta.total` — a
        // regression test for the bug where the dashboard showed "0
        // Formularios Dinámicos" despite forms existing, because it was
        // looking for a pagination envelope that this endpoint never sends.
        $formService = $this->createMock(FormApiService::class);
        $formService->expects($this->once())
            ->method('list')
            ->with([])
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => [
                    ['id' => 1, 'form_key' => 'contact'],
                    ['id' => 2, 'form_key' => 'gdpr_rights'],
                ],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('formApiService', $formService);

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
        $submissionService = $this->createMock(FormSubmissionApiService::class);
        $submissionService->expects($this->once())
            ->method('counts')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['new' => 3, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('formSubmissionApiService', $submissionService);

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
        $submissionService = $this->createMock(FormSubmissionApiService::class);
        $submissionService->expects($this->once())
            ->method('counts')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['new' => 0, 'read' => 5, 'replied' => 2, 'spam' => 0, 'archived' => 1],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('formSubmissionApiService', $submissionService);

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
        $pageService = $this->createMock(PageApiService::class);
        $pageService->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['data' => [
                    ['id' => 1, 'slug' => 'inicio', 'updated_at' => '2026-07-01 00:00:00', 'translations' => [['title' => 'Old Home Page']]],
                ]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageService);

        $entryService = $this->createMock(EntryApiService::class);
        $entryService->expects($this->once())
            ->method('list')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['data' => [
                    ['id' => 5, 'slug' => 'noticia-reciente', 'updated_at' => '2026-07-20 12:00:00', 'translations' => [['title' => 'Recent News']]],
                ]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('entryApiService', $entryService);

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
}
