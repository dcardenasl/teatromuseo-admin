<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\BlockCatalogServiceInterface;
use App\Modules\Cms\Services\BlockTypeOptionsResolver;
use App\Modules\Cms\Services\CollectionApiService;
use App\Modules\Cms\Services\EntryApiService;
use App\Modules\Cms\Services\FormApiService;
use App\Modules\Cms\Services\PageApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Covers two performance fixes on the same hot path (loading "Bloques de
 * Contenido" against 63 active block types):
 *  - resolve() used to re-fetch the active collections / active forms lists
 *    once per block type that declared a collection_key/collection_id/
 *    form_key field instead of once per request, turning a single page load
 *    into 15-20+ redundant HTTP round trips.
 *  - index()/children() (list-only views that never render config_fields/
 *    schema_definition options) called the full resolve() anyway, paying for
 *    all of the above even though none of it was used — rawIndexed() gives
 *    them the plain catalog with zero extra API calls.
 *
 * @internal
 */
final class BlockTypeOptionsResolverTest extends CIUnitTestCase
{
    /**
     * List/read-only views (block index, children list) only render static
     * metadata (name/icon/block_key) and never touch config_fields/
     * schema_definition options — rawIndexed() must return the catalog
     * without making any of resolve()'s forms/collections/pages/entries
     * calls, even when block types declare those config fields.
     */
    public function testRawIndexedNeverTouchesFormsOrCollectionsApis(): void
    {
        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn([
            1 => [
                'id' => 1,
                'name' => 'Grilla de Colección',
                'block_key' => 'collection_grid',
                'schema_definition' => ['config_fields' => ['collection_id' => ['type' => 'text']]],
            ],
            2 => [
                'id' => 2,
                'name' => 'Formulario',
                'block_key' => 'form_embed',
                'schema_definition' => ['config_fields' => ['form_key' => ['type' => 'text']]],
            ],
        ]);

        $formApiClient = $this->createMock(ApiClientInterface::class);
        $formApiClient->expects($this->never())->method('get');
        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->never())->method('get');
        $pageApiClient = $this->createMock(ApiClientInterface::class);
        $pageApiClient->expects($this->never())->method('get');
        $entryApiClient = $this->createMock(ApiClientInterface::class);
        $entryApiClient->expects($this->never())->method('get');

        $resolver = $this->makeResolver($catalog, $formApiClient, $collectionApiClient, $pageApiClient, $entryApiClient);

        $result = $resolver->rawIndexed();

        $this->assertSame('Grilla de Colección', $result[1]['name']);
        $this->assertArrayNotHasKey('options', $result[1]['schema_definition']['config_fields']['collection_id']);
    }

    public function testResolveFetchesCollectionsAtMostOnceAcrossManyBlockTypes(): void
    {
        $blockTypes = [];
        for ($i = 1; $i <= 10; $i++) {
            $blockTypes[$i] = [
                'id' => $i,
                'block_key' => 'collection_grid_' . $i,
                'schema_definition' => [
                    'config_fields' => [
                        'collection_id' => ['type' => 'text'],
                    ],
                ],
            ];
        }

        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn($blockTypes);

        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->once())
            ->method('get')
            ->with('/cms/collections', $this->anything())
            ->willReturn($this->okResponse([
                ['id' => 1, 'collection_key' => 'noticias', 'name' => 'Noticias'],
            ]));

        $formApiClient = $this->createMock(ApiClientInterface::class);
        $formApiClient->expects($this->never())->method('get');
        $pageApiClient = $this->createMock(ApiClientInterface::class);
        $pageApiClient->expects($this->never())->method('get');
        $entryApiClient = $this->createMock(ApiClientInterface::class);
        $entryApiClient->expects($this->never())->method('get');

        $resolver = $this->makeResolver($catalog, $formApiClient, $collectionApiClient, $pageApiClient, $entryApiClient);

        $resolved = $resolver->resolve();

        $this->assertCount(10, $resolved);
        foreach ($resolved as $blockType) {
            $this->assertSame(
                [['value' => 1, 'label' => 'Noticias']],
                $blockType['schema_definition']['config_fields']['collection_id']['options']
            );
        }
    }

    public function testResolveFetchesFormsAtMostOnceAcrossManyFormEmbedBlockTypes(): void
    {
        $blockTypes = [];
        for ($i = 1; $i <= 5; $i++) {
            $blockTypes[$i] = [
                'id' => $i,
                'block_key' => 'form_embed',
                'schema_definition' => [
                    'config_fields' => [
                        'form_key' => ['type' => 'text'],
                    ],
                ],
            ];
        }

        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn($blockTypes);

        $formApiClient = $this->createMock(ApiClientInterface::class);
        $formApiClient->expects($this->once())
            ->method('get')
            ->with('/cms/forms', $this->anything())
            ->willReturn($this->okResponse([
                ['form_key' => 'contact'],
            ]));

        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->never())->method('get');
        $pageApiClient = $this->createMock(ApiClientInterface::class);
        $entryApiClient = $this->createMock(ApiClientInterface::class);

        $resolver = $this->makeResolver($catalog, $formApiClient, $collectionApiClient, $pageApiClient, $entryApiClient);

        $resolved = $resolver->resolve();

        foreach ($resolved as $blockType) {
            $this->assertSame(['contact'], $blockType['schema_definition']['config_fields']['form_key']['options']);
        }
    }

    public function testCollectionsMapReusesTheSameFetchAsResolve(): void
    {
        $catalog = $this->createMock(BlockCatalogServiceInterface::class);
        $catalog->method('indexed')->willReturn([
            1 => [
                'id' => 1,
                'block_key' => 'collection_grid',
                'schema_definition' => ['config_fields' => ['collection_id' => ['type' => 'text']]],
            ],
        ]);

        $collectionApiClient = $this->createMock(ApiClientInterface::class);
        $collectionApiClient->expects($this->once())
            ->method('get')
            ->willReturn($this->okResponse([
                ['id' => 7, 'collection_key' => 'noticias'],
            ]));

        $resolver = $this->makeResolver(
            $catalog,
            $this->createMock(ApiClientInterface::class),
            $collectionApiClient,
            $this->createMock(ApiClientInterface::class),
            $this->createMock(ApiClientInterface::class),
        );

        $resolver->resolve();
        $map = $resolver->collectionsMap();

        $this->assertSame(['noticias' => 7], $map);
    }

    private function makeResolver(
        BlockCatalogServiceInterface $catalog,
        ApiClientInterface $formApiClient,
        ApiClientInterface $collectionApiClient,
        ApiClientInterface $pageApiClient,
        ApiClientInterface $entryApiClient,
    ): BlockTypeOptionsResolver {
        return new BlockTypeOptionsResolver(
            $catalog,
            new FormApiService($formApiClient),
            new CollectionApiService($collectionApiClient),
            new PageApiService($pageApiClient),
            new EntryApiService($entryApiClient),
        );
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return array{ok: bool, status: int, data: array<string, mixed>, raw: string, headers: array<string, string>, messages: list<string>, fieldErrors: array<string, string>}
     */
    private function okResponse(array $items): array
    {
        return [
            'ok' => true,
            'status' => 200,
            'data' => ['data' => $items],
            'raw' => '',
            'headers' => [],
            'messages' => [],
            'fieldErrors' => [],
        ];
    }
}
