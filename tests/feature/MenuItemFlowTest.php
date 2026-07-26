<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\LanguageApiService;
use App\Modules\Cms\Services\MenuApiService;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Fixtures\AdminFixtureFactory;

/**
 * @internal
 */
final class MenuItemFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    public function testCreateRendersEntryAndCollectionSelectors(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $menu = $fixtures->menu();
        $page = ['id' => $fixtures->id('page'), 'translations' => [['title' => $fixtures->value('page-title')]]];
        $entry = ['id' => $fixtures->id('entry'), 'title' => $fixtures->value('entry-title')];
        $collection = $fixtures->collection([]);
        $languages = $fixtures->languages();

        $menuMock = $this->createMock(MenuApiService::class);
        $menuMock->method('get')
            ->with((string) $menu['id'])
            ->willReturn($fixtures->response($menu));
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === (string) $menu['id']))
            ->willReturn($fixtures->response([]));
        Services::injectMock('menuApiService', $menuMock);

        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('pages')
            ->willReturn($fixtures->response(['items' => [$page]]));
        Services::injectMock('pageApiService', $pageMock);

        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->method('list')
            ->willReturn($fixtures->response(['items' => [$entry]]));
        $entryMock->method('collections')
            ->willReturn($fixtures->response(['items' => [$collection]]));
        Services::injectMock('entryApiService', $entryMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')
            ->willReturn($fixtures->response($languages));
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->get('/admin/cms/menus/' . $menu['id'] . '/items/create');

        $result->assertStatus(200);
        $this->assertStringContainsString('name="entry_id"', (string) $result->getBody());
        $this->assertStringContainsString('name="collection_id"', (string) $result->getBody());
        $this->assertStringContainsString('value="entry"', (string) $result->getBody());
        $this->assertStringContainsString('value="collection_listing"', (string) $result->getBody());
    }

    public function testCreateStillRendersWhenOptionServicesFail(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $menu = $fixtures->menu();
        $failure = [
            'ok' => false,
            'status' => 502,
            'data' => null,
            'raw' => '',
            'headers' => [],
            'messages' => ['Upstream unavailable'],
            'fieldErrors' => [],
        ];

        $menuMock = $this->createMock(MenuApiService::class);
        $menuMock->method('get')
            ->with((string) $menu['id'])
            ->willReturn($fixtures->response($menu));
        $menuMock->method('listItems')
            ->willReturn($fixtures->response([]));
        Services::injectMock('menuApiService', $menuMock);

        $pageMock = $this->createMock(PageApiService::class);
        $pageMock->method('pages')->willReturn($failure);
        Services::injectMock('pageApiService', $pageMock);

        $entryMock = $this->createMock(EntryApiService::class);
        $entryMock->method('list')->willReturn($failure);
        $entryMock->method('collections')->willReturn($failure);
        Services::injectMock('entryApiService', $entryMock);

        $langMock = $this->createMock(LanguageApiService::class);
        $langMock->method('list')->willReturn($failure);
        Services::injectMock('languageApiService', $langMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->get('/admin/cms/menus/' . $menu['id'] . '/items/create');

        // Every options() helper hits a failing upstream — the page must still
        // render (empty selectors) instead of a fatal, and the dev error panel
        // path (maybeFlashDevError) must be reachable without throwing.
        $result->assertStatus(200);
    }

    public function testUpdateAcceptsCollectionListingTarget(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $menu = $fixtures->menu();
        $item = $fixtures->menuItem($menu['id'], 4);
        $collection = $fixtures->collection([]);
        $label = $fixtures->value('menu-label');
        $language = $fixtures->languages(3)[0];

        $menuMock = $this->createMock(MenuApiService::class);
        $menuMock->expects($this->once())
            ->method('updateItem')
            ->with((string) $item['id'], $this->callback(static function (array $payload) use ($menu, $collection, $label): bool {
                return $payload['menu_id'] === $menu['id']
                    && $payload['link_type'] === 'collection_listing'
                    && $payload['page_id'] === null
                    && $payload['entry_id'] === null
                    && $payload['collection_id'] === $collection['id']
                    && $payload['sort_order'] === 4
                    && $payload['translations'][0]['label'] === $label;
            }))
            ->willReturn($fixtures->response(['id' => $item['id']]));
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->post('/admin/cms/menus/' . $menu['id'] . '/items/' . $item['id'], [
            csrf_token() => csrf_hash(),
            'menu_id' => (string) $menu['id'],
            'parent_id' => '',
            'link_type' => 'collection_listing',
            'collection_id' => (string) $collection['id'],
            'link_target' => '_self',
            'icon' => '',
            'css_class' => '',
            'sort_order' => '4',
            'is_active' => '1',
            'translations' => [
                1 => [
                    'label' => $label,
                ],
            ],
        ]);

        $result->assertRedirectTo(site_url('admin/cms/menus/' . $menu['id']));
    }

    public function testReorderItemsRendersComponent(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $menu = $fixtures->menu();
        $firstItem = $fixtures->menuItem($menu['id'], 1);
        $secondItem = $fixtures->menuItem($menu['id'], 0);

        $menuMock = $this->createMock(MenuApiService::class);
        $menuMock->method('get')
            ->with((string) $menu['id'])
            ->willReturn($fixtures->response($menu));
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === (string) $menu['id']))
            ->willReturn($fixtures->response([
                $firstItem + ['label' => $fixtures->value('item-label', 'first')],
                $secondItem + ['label' => $fixtures->value('item-label', 'second')],
            ]));
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->get('/admin/cms/menus/' . $menu['id'] . '/items/reorder');

        $result->assertStatus(200);
        $this->assertStringContainsString('Reordenar', (string) $result->getBody());
    }

    public function testSaveItemsOrderUpdatesAndReturnsOk(): void
    {
        $fixtures = new AdminFixtureFactory(__METHOD__);
        $menu = $fixtures->menu();
        $firstItem = $fixtures->menuItem($menu['id'], 1);
        $secondItem = $fixtures->menuItem($menu['id'], 0);

        $menuMock = $this->createMock(MenuApiService::class);
        $menuMock->method('listItems')
            ->with($this->callback(static fn (array $filters): bool => ($filters['menu_id'] ?? null) === (string) $menu['id']))
            ->willReturn($fixtures->response([
                $firstItem + ['translations' => []],
                $secondItem + ['translations' => []],
            ]));
        $menuMock->expects($this->exactly(2))
            ->method('updateItem')
            ->willReturnCallback(static function (string $id, array $payload) use ($secondItem, $firstItem): array {
                if ($id === (string) $secondItem['id']) {
                    self::assertSame(0, $payload['sort_order']);
                } elseif ($id === (string) $firstItem['id']) {
                    self::assertSame(1, $payload['sort_order']);
                }
                return ['ok' => true];
            });
        Services::injectMock('menuApiService', $menuMock);

        $result = $this->withSession([
            'access_token' => 'token',
            'user'         => ['permissions' => ['cms.menus.write', 'cms.menus.read']],
        ])->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            csrf_header()      => csrf_hash(),
            'Content-Type'     => 'application/json',
        ])->withBody(json_encode([
            'items' => [
                ['id' => $secondItem['id'], 'sort_order' => 0],
                ['id' => $firstItem['id'], 'sort_order' => 1],
            ],
        ]))->post('/admin/cms/menus/' . $menu['id'] . '/items/reorder');

        $result->assertStatus(200);
        $this->assertStringContainsString('"ok": true', (string) $result->getBody());
    }
}
