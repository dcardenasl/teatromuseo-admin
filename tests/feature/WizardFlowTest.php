<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Libraries\DomainApiClientInterface;
use App\Modules\Cms\Controllers\StructureWizardController;
use App\Modules\Cms\Controllers\WizardController;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Fixtures\AdminFixtureFactory;

/**
 * @internal
 */
final class WizardFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/wizard');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/wizard');

        $result->assertStatus(200);
        $result->assertSee('¿Qué quieres hacer hoy?');
        $result->assertSee('Volver al panel');
        $result->assertSee('Reintentar');
    }

    public function testTranslateProxyDoesNotRequirePageReadPermission(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/translate');

        $result->assertStatus(400);
        $this->assertStringContainsString('Missing required parameters', (string) $result->getBody());
    }

    public function testContentWizardRendersTranslationReviewSection(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/wizard');

        $result->assertStatus(200);
        $body = html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertStringContainsString('Revisa el contenido antes de publicar', $body);
        $this->assertStringContainsString('Traducciones automáticas', $body);
        $this->assertStringContainsString('Idiomas listos', $body);
        $this->assertStringContainsString('Generando traducciones...', $body);
        $this->assertStringContainsString('Proceso completado', $body);
        $this->assertStringContainsString('Acciones', $body);
    }

    public function testContentWizardRichTextBlockStepsSyncDraftLive(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user'         => ['permissions' => ['cms.entries.read']],
        ])->get('/admin/cms/wizard');

        $result->assertStatus(200);
        $view = file_get_contents(APPPATH . 'Views/cms/wizard/_partials/entry_wizard.php');
        $this->assertIsString($view);
        $this->assertStringContainsString('data-wizard-content-richtext-field', $view);
        $this->assertStringContainsString('syncBlockContentRichTextDraft(blockContentStepIndex, field.key, $event.target.value)', $view);

        $wizardModule = file_get_contents(ROOTPATH . 'src/js/components/wizard/entryPublish.js');
        $this->assertIsString($wizardModule);
        $this->assertStringContainsString('syncBlockContentRichTextDraft(stepIdx, key, value)', $wizardModule);
        $this->assertStringContainsString("field.uiType === 'richtext'", $wizardModule);

        $asset = file_get_contents(FCPATH . 'assets/js/app.js');
        $this->assertIsString($asset);
        $this->assertStringContainsString('dispatchEvent(new window.Event("input",{bubbles:!0}))', $asset);
    }

    public function testStructureWizardIndexRendersForAdmin(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $languages = $fixtures->languages();
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user' => ['permissions' => [
                'cms.entries.read',
                'cms.collections.read',
                'cms.collections.write',
                'cms.pages.read',
                'cms.pages.write',
                'cms.menus.read',
                'cms.menus.write',
            ]],
        ])->get('/admin/cms/wizard/structure');

        $result->assertStatus(200);
        $result->assertSee('¿Qué quieres construir hoy?');
        $result->assertSee('Crear colección');
        $result->assertSee('Crear menú');
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Resumen del preset', $body);
        $this->assertStringContainsString('Idiomas habilitados', $body);
        $this->assertStringContainsString('collection_translation_name_0', $body);
        $this->assertStringContainsString('collection_translation_slug_0', $body);
        $this->assertStringContainsString('collectionErrors.slug_base', $body);
        $this->assertStringContainsString('data-slug-invalid-message', $body);
        $this->assertStringNotContainsString('name="collection_type"', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
        $this->assertStringContainsString('Idioma base', $body);
    }

    public function testStructureWizardIndexStillRendersWhenLanguageServiceFails(): void
    {
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn([
                'ok' => false,
                'status' => 502,
                'data' => null,
                'raw' => '',
                'headers' => [],
                'messages' => ['Upstream unavailable'],
                'fieldErrors' => [],
            ]);
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user' => ['permissions' => [
                'cms.entries.read',
                'cms.collections.read',
                'cms.collections.write',
                'cms.pages.read',
                'cms.pages.write',
                'cms.menus.read',
                'cms.menus.write',
            ]],
        ])->get('/admin/cms/wizard/structure');

        // languageApiService failing must not crash the wizard's structure
        // screen — it renders with an empty language list instead, and the
        // dev error panel path (maybeFlashDevError) must be reachable
        // without throwing.
        $result->assertStatus(200);
    }

    public function testStructureWizardRedirectsForCMSReaderWithoutWritePermissions(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'permissions_refreshed_at' => time(),
            'user' => ['permissions' => [
                'cms.entries.read',
                'cms.pages.read',
                'cms.menus.read',
                'cms.collections.read',
            ]],
        ])->get('/admin/cms/wizard/structure');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testStructureWizardConfigAccessDeniedWithoutWritePermissions(): void
    {
        session()->set('user', ['permissions' => ['cms.pages.read']]);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(403, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertFalse($body['ok']);
        $this->assertSame(lang('App.access_denied'), $body['message']);
    }

    public function testStructureWizardConfigExposesActiveLanguagesWithoutDefaultLanguageId(): void
    {
        session()->set('user', ['permissions' => ['cms.collections.write']]);
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $languages = $fixtures->languages();
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->expects($this->once())
            ->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('languages', $body['data']);
        $this->assertArrayHasKey('collection_presets', $body['data']);
        $this->assertArrayNotHasKey('default_language_id', $body['data']);
        $this->assertTrue($body['data']['languages'][0]['is_default']);
        $this->assertArrayHasKey('setup_state', $body['data']);
        $this->assertArrayHasKey('collection_presets', $body['data']);
    }

    public function testStructureWizardCreateCollectionAcceptsDynamicTypeWithoutPreset(): void
    {
        session()->set('user', ['permissions' => ['cms.collections.write']]);
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $language = $fixtures->languages(1)[0];
        $collectionType = $fixtures->value('collection-type');
        $collectionKey = $fixtures->value('collection-key');

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload) use ($collectionType): bool {
                return ($payload['collection_type'] ?? null) === $collectionType
                    && ($payload['block_template'] ?? null) === null
                    && ($payload['wizard_config'] ?? null) === null;
            }))
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['id' => 33, 'collection_key' => 'case-studies'],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $this->injectJsonBody([
            'collection_type' => $collectionType,
            'collection_key' => $collectionKey,
            'sort_order' => 0,
            'block_template' => null,
            'wizard_config' => null,
            'translations' => [
                ['language_id' => $language['id'], 'slug' => $collectionKey, 'name' => $fixtures->value('collection-name')],
            ],
        ]);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createCollection();

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testConfigUnwrapsDomainPayload(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $language = $fixtures->languages(1)[0];
        $collection = $fixtures->collection([]);
        $pageId = $fixtures->id('page');
        $menuId = $fixtures->id('menu');
        $blockTypeId = $fixtures->id('block-type');
        $mock = $this->createMock(DomainApiClientInterface::class);
        $configResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'status' => 'success',
                'data' => [
                    'default_language_id' => $language['id'],
                    'languages' => [$language],
                    'collections' => [
                        ['id' => $collection['id'], 'name' => $fixtures->value('collection-name')],
                    ],
                    'pages' => [
                        ['id' => $pageId, 'title' => $fixtures->value('page-title')],
                    ],
                    'menus' => [
                        ['id' => $menuId, 'name' => $fixtures->value('menu-name')],
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $blockTypesResponse = [
            'ok' => true,
            'status' => 200,
            'data' => [
                'items' => [
                    [
                        'id' => $blockTypeId,
                        'block_key' => 'rich_text',
                        'name' => 'Rich Text',
                        'description' => 'Editor de texto enriquecido',
                        'icon' => 'align-left',
                        'schema_definition' => ['fields' => []],
                        'supports_pages' => true,
                        'supports_entries' => true,
                        'is_container' => false,
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];

        $mock->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($configResponse, $blockTypesResponse);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->config();

        $this->assertSame(200, $result->getStatusCode());

        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('collections', $body);
        $this->assertArrayHasKey('pages', $body);
        $this->assertArrayHasKey('menus', $body);
        $this->assertArrayHasKey('default_language_id', $body);
        $this->assertArrayHasKey('collection_types', $body);
        $this->assertArrayHasKey('page_types', $body);
        $this->assertArrayHasKey('languages', $body);
        $this->assertSame($language['id'], $body['default_language_id']);
        $this->assertTrue($body['languages'][0]['is_default']);
        $this->assertCount(1, $body['collections']);
        $this->assertCount(1, $body['pages']);
        $this->assertCount(1, $body['menus']);
        $this->assertNotEmpty($body['collection_types']);
        $this->assertNotEmpty($body['page_types']);
        $this->assertSame($blockTypeId, $body['block_types']['rich_text']['id']);
        $this->assertTrue($body['block_types']['rich_text']['supports_entries']);
    }

    public function testEntryBlocksUnwrapsDomainPayload(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/entries/7/blocks', ['include_translations' => 1, 'limit' => 100])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'items' => [
                        ['id' => 99, 'block_config' => ['block_key' => 'rich_text']],
                    ],
                    'raw' => '',
                    'headers' => [],
                    'messages' => [],
                    'fieldErrors' => [],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->entryBlocks(7);

        $this->assertSame(200, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('items', $body);
        $this->assertSame(99, $body['items'][0]['id']);
    }

    // ── publish() validation ──────────────────────────────────────────────────

    public function testPublishRejectsEmptyPayload(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(400, $result->getStatusCode());
    }

    public function testPublishRejectsMissingCollectionId(): void
    {
        $this->injectJsonBody(['title' => 'Test', 'status' => 'draft']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('collection_id', $body['errors']);
    }

    public function testPublishRejectsMissingTitle(): void
    {
        $this->injectJsonBody(['collection_id' => 1, 'title' => '  ', 'status' => 'draft']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('title', $body['errors']);
    }

    public function testPublishRejectsInvalidStatus(): void
    {
        $this->injectJsonBody(['collection_id' => 1, 'title' => 'Test', 'status' => 'scheduled']);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertArrayHasKey('status', $body['errors']);
    }

    public function testPublishForwardsValidPayloadToDomain(): void
    {
        $payload = ['collection_id' => 1, 'title' => 'My Entry', 'status' => 'published'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 42, 'title' => 'My Entry'],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->publish();

        $this->assertSame(201, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertSame(42, $body['id']);
    }

    public function testStructureWizardCreateCollectionSurfacesApiValidationDetail(): void
    {
        session()->set('user', ['permissions' => ['cms.collections.write']]);
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $language = $fixtures->languages(1)[0];
        $collectionKey = $fixtures->value('collection-key');

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->willReturn([
                'ok' => false,
                'status' => 422,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ['Slug already exists'],
                'fieldErrors' => ['collection_key' => 'Slug already exists'],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $this->injectJsonBody([
            'collection_type' => $fixtures->value('collection-type'),
            'collection_key' => $collectionKey,
            'sort_order' => 0,
            'translations' => [
                ['language_id' => $language['id'], 'slug' => $collectionKey, 'name' => $fixtures->value('collection-name')],
            ],
        ]);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createCollection();

        $this->assertSame(422, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('Slug already exists', $body['message']);
        $this->assertSame(['collection_key' => 'Slug already exists'], $body['fieldErrors']);
    }

    public function testStructureWizardCreateCollectionRejectsSuccessfulResponseWithoutId(): void
    {
        session()->set('user', ['permissions' => ['cms.collections.write']]);
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $language = $fixtures->languages(1)[0];
        $collectionKey = $fixtures->value('collection-key');

        $collectionMock = $this->createMock(\App\Modules\Cms\Services\CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['collection_key' => $collectionKey],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $this->injectJsonBody([
            'collection_type' => $fixtures->value('collection-type'),
            'collection_key' => $collectionKey,
            'sort_order' => 0,
            'translations' => [
                ['language_id' => $language['id'], 'slug' => $collectionKey, 'name' => $fixtures->value('collection-name')],
            ],
        ]);

        $controller = new StructureWizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createCollection();

        $this->assertSame(502, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertFalse($body['ok']);
        $this->assertSame(lang('Wizard.wizard_structure_error_collection_missing_id'), $body['message']);
        $this->assertArrayNotHasKey('fieldErrors', $body);
    }

    // ── uploadImage() validation ──────────────────────────────────────────────

    public function testValidateImageFileRejectsNonImageMime(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'application/pdf';
            }
            public function getSize(): int
            {
                return 1024;
            }
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertIsString($error);
        $this->assertStringContainsString('application/pdf', $error);
    }

    public function testValidateImageFileRejectsOversizedFile(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'image/jpeg';
            }
            public function getSize(): int
            {
                return 20 * 1024 * 1024;
            } // 20 MB
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertIsString($error);
        $this->assertStringContainsString('MB', $error);
    }

    public function testValidateImageFileAcceptsValidImage(): void
    {
        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));

        $fakeFile = new class () {
            public function getMimeType(): string
            {
                return 'image/jpeg';
            }
            public function getSize(): int
            {
                return 500 * 1024;
            } // 500 KB
        };

        $ref = new \ReflectionMethod($controller, 'validateImageFile');
        $ref->setAccessible(true);
        $error = $ref->invoke($controller, $fakeFile);

        $this->assertNull($error);
    }

    // ── createBlock() / createEntryBlock() validation ────────────────────────

    public function testCreateBlockRejectsMissingBlockId(): void
    {
        $this->injectJsonBody(['block_data' => ['text' => 'hello']]);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createBlock(1);

        $this->assertSame(400, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertStringContainsString('block_id', $body['message']);
    }

    public function testCreateBlockForwardsValidPayloadToDomain(): void
    {
        $payload = ['block_id' => 7, 'block_data' => ['text' => 'hello']];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/pages/5/blocks', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 10],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createBlock(5);

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testCreateEntryBlockRejectsMissingBlockId(): void
    {
        $this->injectJsonBody(['block_data' => []]);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createEntryBlock(3);

        $this->assertSame(400, $result->getStatusCode());
        $body = json_decode((string) $result->getBody(), true);
        $this->assertStringContainsString('block_id', $body['message']);
    }

    public function testCreateEntryBlockForwardsValidPayloadToDomain(): void
    {
        $payload = ['block_id' => 12, 'block_data' => ['title' => 'Hi']];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries/3/blocks', $payload)
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 20],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->createEntryBlock(3);

        $this->assertSame(201, $result->getStatusCode());
    }

    // ── menu item mutations ───────────────────────────────────────────────────

    public function testAddMenuItemForwardsPayloadToDomain(): void
    {
        $payload = ['label' => 'Inicio', 'url' => '/'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/menu-items', array_merge($payload, ['menu_id' => 2]))
            ->willReturn([
                'ok' => true, 'status' => 201,
                'data' => ['id' => 7],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->addMenuItem(2);

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testUpdateMenuItemForwardsPayloadToDomain(): void
    {
        $payload = ['label' => 'Contacto', 'url' => '/contacto'];
        $this->injectJsonBody($payload);

        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->method('get')
            ->with('/cms/menu-items/9')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['id' => 9, 'translations' => [['language_id' => 1, 'label' => 'Contacto']]],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        $expectedPayload = array_merge($payload, [
            'translations' => [['language_id' => 1, 'label' => 'Contacto']]
        ]);

        $mock->expects($this->once())
            ->method('put')
            ->with('/cms/menu-items/9', $expectedPayload)
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => ['id' => 9],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->updateMenuItem(9);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testDeleteMenuItemForwardsToDomain(): void
    {
        $mock = $this->createMock(DomainApiClientInterface::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/menu-items/9')
            ->willReturn([
                'ok' => true, 'status' => 200,
                'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('domainApiClient', $mock);

        $controller = new WizardController();
        $controller->initController(Services::request(), Services::response(), Services::logger(true));
        $result = $controller->deleteMenuItem(9);

        $this->assertSame(200, $result->getStatusCode());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Replaces the CI4 request body with a JSON payload so controller methods
     * that call $this->request->getJSON(true) receive the expected data.
     *
     * @param array<string, mixed> $data
     */
    private function injectJsonBody(array $data): void
    {
        $request = Services::request();
        $request->setBody((string) json_encode($data));
        $request->setHeader('Content-Type', 'application/json');
        Services::injectMock('request', $request);
    }
}
