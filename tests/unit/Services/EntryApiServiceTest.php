<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\EntryApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EntryApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/entries', [])
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/entries/uuid-1')
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries', $payload)
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/entries/uuid-3')
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }

    public function testSyncCategoriesCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries/7/categories', ['category_ids' => [2, 5]])
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->syncCategories(7, [2, 5]));
    }

    public function testSyncTagsCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries/7/tags', ['tag_ids' => [3, 8]])
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->syncTags(7, [3, 8]));
    }

    public function testSyncTaxonomyCallsAtomicEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/entries/7/taxonomy', [
                'category_ids' => [2, 5],
                'tag_ids' => [],
            ])
            ->willReturn($expected);

        $service = new EntryApiService($mock);
        $this->assertSame($expected, $service->syncTaxonomy(7, [2, 5], []));
    }
}
