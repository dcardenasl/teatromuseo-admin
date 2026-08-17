<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\BffApiClientInterface;
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

    /** @param array<string, mixed> $sections */
    private function injectPageFormOptions(array $sections): void
    {
        $bff = $this->createMock(BffApiClientInterface::class);
        $bff->expects($this->once())
            ->method('getAdminCmsPageFormOptions')
            ->with(null)
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => ['status' => 'success', 'sections' => $sections],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('bffApiClient', $bff);
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
        $this->injectPageFormOptions(['pages' => [], 'languages' => [], 'collections' => []]);

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
        $this->injectPageFormOptions([
            'pages' => [],
            'languages' => [$language],
            'collections' => [],
        ]);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.pages.write', 'cms.pages.read']],
        ])->get('/admin/cms/pages/create');

        $body = (string) $result->getBody();
        $bodyText = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $result->assertStatus(200);
        $this->assertStringContainsString('name="page_type"', $body);
        $this->assertStringContainsString('Inicio', $body);
        $this->assertStringContainsString('Gen&eacute;rica', $body);
        $this->assertStringContainsString('Colecci&oacute;n del museo', $body);
        $this->assertStringContainsString('&Iacute;ndice de Colecci&oacute;n', $body);
        $this->assertStringContainsString('Plantilla de ficha de catálogo', $bodyText);
        $this->assertStringContainsString('Plantilla de ficha de evento', $bodyText);
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
