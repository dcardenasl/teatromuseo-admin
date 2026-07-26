<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Fixtures\AdminFixtureFactory;

/**
 * @internal
 */
final class CollectionFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testAdminRoutesRequireAuth(): void
    {
        $result = $this->get('/admin/cms/collections');
        $result->assertRedirectTo(site_url('login'));
    }

    public function testNonAdminCannotAccess(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => []],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections');

        $result->assertRedirectTo(site_url('dashboard'));
    }

    public function testIndexRendersForAdmin(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections');

        $result->assertStatus(200);
    }

    public function testEditRendersIdentityWithoutStructureEditor(): void
    {
        $fixtures = new AdminFixtureFactory(self::class);
        $languages = $fixtures->languages();
        $collection = $fixtures->collection([
            [
                'language_id' => $languages[0]['id'],
                'slug' => $fixtures->value('collection-slug', $languages[0]['code']),
                'name' => $fixtures->value('collection-name', $languages[0]['code']),
                'description' => null,
            ],
            [
                'language_id' => $languages[1]['id'],
                'slug' => $fixtures->value('collection-slug', $languages[1]['code']),
                'name' => $fixtures->value('collection-name', $languages[1]['code']),
                'description' => null,
            ],
        ]);
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with((string) $collection['id'])
            ->willReturn($fixtures->response(['id' => $collection['id'], 'collection_key' => $collection['collection_key'], 'translations' => $collection['translations']]));
        $collectionMock->method('checkSlug')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => ['available' => true],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections/' . $collection['id'] . '/edit');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $translationPos = strpos($body, 'name="translations[0][name]"');
        $collectionKeyPos = strpos($body, 'name="collection_key"');
        $namePos = strpos($body, 'name="translations[0][name]"');
        $slugPos = strpos($body, 'name="translations[0][slug]"');
        $this->assertNotFalse($namePos);
        $this->assertNotFalse($slugPos);
        $this->assertNotFalse($translationPos);
        $this->assertNotFalse($collectionKeyPos);
        $this->assertLessThan($slugPos, $namePos);
        $this->assertLessThan($collectionKeyPos, $translationPos);
        $this->assertStringContainsString('data-slug-check-url="', $body);
        $this->assertStringContainsString('data-slug-current-id="' . $collection['id'] . '"', $body);
        $this->assertStringContainsString('name="current_id" value="' . $collection['id'] . '"', $body);
        $this->assertStringContainsString('name="default_language_id"', $body);
        $this->assertStringContainsString("langTabs(" . $languages[0]['id'] . ", '/admin/cms/translate', '" . $languages[0]['code'] . "')", $body);
        $this->assertStringContainsString('autoTranslateAll([', $body);
        $this->assertStringContainsString('name="collection_type"', $body);
        $this->assertStringNotContainsString('name="block_template"', $body);
        $this->assertStringNotContainsString('collectionBlockTemplateBuilder(', $body);
        $this->assertStringContainsString('/structure', $body);
        $this->assertStringContainsString('Estructura de bloques', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
    }

    public function testCreateRendersIdentityWithoutStructureEditor(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $languages = $fixtures->languages();
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections/create');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $translationPos = strpos($body, 'name="translations[0][name]"');
        $collectionKeyPos = strpos($body, 'name="collection_key"');
        $this->assertStringContainsString('name="collection_type"', $body);
        $this->assertStringContainsString('name="collection_key"', $body);
        $this->assertStringContainsString('name="default_language_id"', $body);
        $this->assertStringContainsString("langTabs(" . $languages[0]['id'] . ", '/admin/cms/translate', '" . $languages[0]['code'] . "')", $body);
        $this->assertStringContainsString('autoTranslateAll([', $body);
        $this->assertStringContainsString('data-auto-slug-source=', $body);
        $this->assertStringContainsString('translations[0][name]', $body);
        $this->assertStringNotContainsString('name="block_template"', $body);
        $this->assertStringNotContainsString('collectionBlockTemplateBuilder(', $body);
        $this->assertStringContainsString('La estructura de bloques es opcional', $body);
        $this->assertNotFalse($translationPos);
        $this->assertNotFalse($collectionKeyPos);
        $this->assertLessThan($collectionKeyPos, $translationPos);
    }

    public function testStructurePageRendersDedicatedTemplateEditor(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'news',
                    'collection_type' => 'news',
                    'block_template' => [
                        'version' => '1.0',
                        'blocks' => [
                            [
                                'block_key' => 'rich_text',
                                'label' => 'Contenido',
                                'help_text' => 'Texto principal',
                                'required' => true,
                                'locked' => false,
                                'block_config_defaults' => [],
                            ],
                        ],
                    ],
                    'wizard_config' => [
                        'type' => 'news',
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $collectionMock->method('update')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        Services::injectMock('collectionApiService', $collectionMock);

        $blockCatalogMock = $this->createMock(BlockCatalogServiceInterface::class);
        $blockCatalogMock->expects($this->once())
            ->method('selectableTopLevel')
            ->willReturn([
                [
                    'id' => 1,
                    'block_key' => 'rich_text',
                    'name' => 'Rich text',
                    'description' => 'Editorial block',
                    'icon' => 'layout-template',
                    'supports_entries' => true,
                    'is_child_only' => false,
                ],
                [
                    'id' => 2,
                    'block_key' => 'collection_grid',
                    'name' => 'Collection grid',
                    'description' => 'Collection listing block',
                    'icon' => 'layout-grid',
                    'supports_entries' => false,
                    'is_child_only' => false,
                ],
            ]);
        Services::injectMock('blockCatalogService', $blockCatalogMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/collections/10/structure');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('collectionBlockTemplateBuilder(', $body);
        $this->assertStringContainsString('name="block_template"', $body);
        $this->assertStringContainsString('row.advancedOpen', $body);
        $this->assertStringContainsString('/admin/cms/collections/10/structure', $body);
        $this->assertStringContainsString('data-testid="collection-structure-save"', $body);
        $this->assertStringContainsString('Guardar estructura', $body);
        $this->assertStringContainsString('order-first space-y-6 lg:order-none', $body);
        $this->assertStringContainsString('"block_key":"rich_text"', $body);
        $this->assertStringContainsString('"block_key":"collection_grid"', $body);
    }

    public function testUpdateStructurePersistsWizardConfigStepsRoundTrip(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'news',
                    'collection_type' => 'news',
                    'block_template' => ['version' => '1.0', 'blocks' => []],
                    'wizard_config' => ['type' => 'news', 'steps' => []],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        $postedWizardConfig = [
            'type' => 'news',
            'steps' => [
                ['step_title' => 'Titular', 'step_hint' => '', 'fields' => [
                    ['key' => 'title', 'label' => 'Titular', 'type' => 'text', 'required' => true],
                ]],
                ['step_title' => 'Resumen y meta', 'step_hint' => '', 'fields' => [
                    ['key' => 'excerpt', 'label' => 'Resumen', 'type' => 'textarea', 'required' => false],
                    ['key' => 'meta_title', 'label' => 'Meta título', 'type' => 'text', 'required' => false],
                ]],
            ],
        ];

        $collectionMock->expects($this->once())
            ->method('update')
            ->with('10', $this->callback(static function (array $payload) use ($postedWizardConfig): bool {
                return ($payload['wizard_config'] ?? null) === $postedWizardConfig;
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10/structure', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'block_template' => json_encode(['version' => '1.0', 'blocks' => []], JSON_THROW_ON_ERROR),
            'wizard_config' => json_encode($postedWizardConfig, JSON_THROW_ON_ERROR),
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('success');
    }

    public function testUpdateStructureSurfacesWizardConfigValidationErrorFromApi(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'news',
                    'collection_type' => 'news',
                    'block_template' => ['version' => '1.0', 'blocks' => []],
                    'wizard_config' => ['type' => 'news', 'steps' => []],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $collectionMock->method('update')
            ->willReturn([
                'ok' => false,
                'status' => 422,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => ["Field 'custom_field': not part of the native field catalog"],
                'fieldErrors' => ['wizard_config' => "Field 'custom_field': not part of the native field catalog"],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $invalidWizardConfig = [
            'type' => 'news',
            'steps' => [
                ['step_title' => 'Paso', 'step_hint' => '', 'fields' => [
                    ['key' => 'custom_field', 'label' => 'Campo custom', 'type' => 'text', 'required' => false],
                ]],
            ],
        ];

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10/structure', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'block_template' => json_encode(['version' => '1.0', 'blocks' => []], JSON_THROW_ON_ERROR),
            'wizard_config' => json_encode($invalidWizardConfig, JSON_THROW_ON_ERROR),
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('fieldErrors');
    }

    public function testStructureWizardShowsCollectionTypeAndHidesLegacyLanguageFields(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $languages = $fixtures->languages();
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read', 'cms.entries.read', 'cms.pages.read', 'cms.pages.write', 'cms.menus.read', 'cms.menus.write']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/wizard/structure');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString('Resumen del preset', $body);
        $this->assertStringContainsString('Usar preset recomendado', $body);
        $this->assertStringContainsString('Crear sin preset', $body);
        $this->assertStringContainsString('Idiomas habilitados', $body);
        $this->assertStringContainsString('collection_translation_name_0', $body);
        $this->assertStringContainsString('collection_translation_slug_0', $body);
        $this->assertStringContainsString('Traducir todo', $body);
        $this->assertStringContainsString('Incluir', $body);
        $this->assertStringNotContainsString('name="collection_type"', $body);
        $this->assertStringContainsString('Idioma base', $body);
        $this->assertStringNotContainsString('name="url_prefix"', $body);
        $this->assertStringNotContainsString('default_language_id', $body);
    }

    public function testStructureWizardShowsDedicatedCollectionSuccessScreen(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $languages = $fixtures->languages(1);
        $languageMock = $this->createMock(LanguageApiService::class);
        $languageMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $languageMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read', 'cms.entries.read', 'cms.pages.read', 'cms.pages.write', 'cms.menus.read', 'cms.menus.write']],
            'permissions_refreshed_at' => time(),
        ])->get('/admin/cms/wizard/structure');

        $body = (string) $result->getBody();
        $result->assertStatus(200);
        $this->assertStringContainsString("x-show=\"screen === 'collection-success'\"", $body);
        $this->assertStringNotContainsString('lg:grid-cols-3', $body);
        $this->assertStringContainsString(lang('Wizard.wizard_structure_create_first_entry'), $body);
    }

    public function testStoreValidationFailureRedirectsBack(): void
    {
        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirect();
    }

    public function testStoreDerivesCollectionKeyFromDefaultLanguageNameWhenMissing(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['collection_key'] ?? null) === 'case-studies'
                    && ($payload['translations'][0]['name'] ?? null) === 'Case Studies'
                    && ($payload['translations'][0]['language_id'] ?? null) === 1;
            }))
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['id' => 45],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
            'default_language_id' => '1',
            'collection_type' => 'case-studies',
            'collection_key' => '',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Case Studies',
                    'slug' => 'should-not-be-used',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    public function testUpdateAllowsKeepingTheSameCollectionKey(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'news',
                    'collection_type' => 'news',
                    'block_template' => [
                        'version' => '1.0',
                        'blocks' => [],
                    ],
                    'wizard_config' => [
                        'type' => 'news',
                    ],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $collectionMock->expects($this->once())
            ->method('update')
            ->with('10', $this->callback(static function (array $payload): bool {
                return ($payload['collection_key'] ?? null) === 'news'
                    && ($payload['collection_type'] ?? null) === 'news'
                    && isset($payload['block_template'])
                    && isset($payload['wizard_config']);
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'collection_type' => 'news',
            'collection_key' => 'news',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'News',
                    'slug' => 'news',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    /**
     * COL-001 regression: before this fix, `update()` always overwrote the posted
     * `collection_type` with whatever the API's current record had, so the field could never
     * actually be changed through the edit form even once it existed in the view.
     */
    public function testUpdateAppliesNewCollectionType(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'noticias',
                    'collection_type' => 'news',
                    'block_template' => ['version' => '1.0', 'blocks' => []],
                    'wizard_config' => ['type' => 'news'],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $collectionMock->expects($this->once())
            ->method('update')
            ->with('10', $this->callback(static function (array $payload): bool {
                return ($payload['collection_type'] ?? null) === 'eventos';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'collection_type' => 'eventos',
            'collection_key' => 'noticias',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Noticias',
                    'slug' => 'noticias',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    /**
     * COL-001 regression: a request that omits `collection_type` entirely (e.g. a direct API
     * call, not the edit form) must keep the collection's current type rather than falling back
     * to CollectionStoreRequest::payload()'s internal 'other' default and silently clobbering it.
     */
    public function testUpdateOmittingCollectionTypeKeepsCurrentValue(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->method('get')
            ->with('10')
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    'id' => 10,
                    'collection_key' => 'noticias',
                    'collection_type' => 'news',
                    'block_template' => ['version' => '1.0', 'blocks' => []],
                    'wizard_config' => ['type' => 'news'],
                ],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);
        $collectionMock->expects($this->once())
            ->method('update')
            ->with('10', $this->callback(static function (array $payload): bool {
                return ($payload['collection_type'] ?? null) === 'news';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/10', [
            csrf_token() => csrf_hash(),
            'current_id' => '10',
            'collection_key' => 'noticias',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Noticias',
                    'slug' => 'noticias',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    public function testStoreAcceptsDynamicCollectionType(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['collection_type'] ?? null) === 'case-studies'
                    && ($payload['collection_key'] ?? null) === 'case-studies';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['id' => 44],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
            'collection_type' => 'case-studies',
            'collection_key' => 'case-studies',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Case Studies',
                    'slug' => 'case-studies',
                    'description' => '',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    /**
     * COL-002 regression: CollectionStoreRequest::normalizeTranslations() rebuilds each
     * translation row from an explicit field list (language_id/slug/name/description) — adding
     * a new per-language field to the view without also adding it here silently drops it before
     * it ever reaches the API, even though the field renders and looks like it saved.
     */
    public function testStorePersistsEntryCtaLabelPerLanguage(): void
    {
        $collectionMock = $this->createMock(CollectionApiService::class);
        $collectionMock->expects($this->once())
            ->method('create')
            ->with($this->callback(static function (array $payload): bool {
                return ($payload['translations'][0]['entry_cta_label'] ?? null) === 'Ver evento';
            }))
            ->willReturn([
                'ok' => true,
                'status' => 201,
                'data' => ['id' => 45],
                'raw' => '',
                'headers' => [],
                'messages' => [],
                'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $collectionMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections', [
            csrf_token() => csrf_hash(),
            'collection_type' => 'eventos',
            'collection_key' => 'eventos',
            'translations' => [
                [
                    'language_id' => '1',
                    'name' => 'Eventos',
                    'slug' => 'eventos',
                    'description' => '',
                    'entry_cta_label' => 'Ver evento',
                ],
            ],
        ]);

        $result->assertRedirect();
    }

    public function testDeleteSuccessRedirectsToList(): void
    {
        $mock = $this->createMock(CollectionApiService::class);
        $mock->expects($this->once())
            ->method('delete')
            ->with('test-uuid')
            ->willReturn([
                'ok' => true, 'status' => 200, 'data' => [],
                'raw' => '', 'headers' => [], 'messages' => [], 'fieldErrors' => [],
            ]);

        Services::injectMock('collectionApiService', $mock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.collections.write', 'cms.collections.read']],
            'permissions_refreshed_at' => time(),
        ])->post('/admin/cms/collections/test-uuid/delete', [
            csrf_token() => csrf_hash(),
        ]);

        $result->assertRedirectTo(site_url('admin/cms/collections'));
    }
}
