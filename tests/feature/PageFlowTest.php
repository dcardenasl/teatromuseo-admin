<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Fixtures\AdminFixtureFactory;

/**
 * @internal
 */
final class PageFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/pages');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
        ])->get('/admin/cms/pages');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $mock = $this->createMock(PageApiService::class);
        $mock->method('pages')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.read']],
        ])->get('/admin/cms/pages');

        $result->assertStatus(200);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testCreateRendersPresetDrivenPageTypes(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $language = $fixtures->languages(1)[0];
        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('pages')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('pageApiService', $pageMock);

        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('defaultId')
            ->willReturn($language['id']);
        $languageMock->method('list')
            ->willReturn($fixtures->response([$language]));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->get('/admin/cms/pages/create');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('name="page_type"', $body);
        // Editorial classifications (about/history/events) were deliberately
        // normalized to generic pages by the domain migration. The admin must
        // expose only the page types that the current Domain contract accepts.
        $this->assertStringContainsString('Inicio', $body);
        $this->assertStringContainsString('Gen&eacute;rica', $body);
        $this->assertStringContainsString('&Iacute;ndice de Colecci&oacute;n', $body);
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(PageApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('pageApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->post('/admin/cms/pages/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/pages'));
    }
}
