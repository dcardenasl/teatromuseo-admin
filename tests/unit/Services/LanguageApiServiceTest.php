<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Cms\Services\LanguageApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LanguageApiServiceTest extends CIUnitTestCase
{
    public function testListCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/languages', [])
            ->willReturn($expected);

        $service = new LanguageApiService($mock);
        $this->assertSame($expected, $service->list());
    }

    public function testDefaultIdPrefersExplicitDefaultLanguage(): void
    {
        $mock = $this->createMock(ApiClientInterface::class);

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/languages', ['limit' => 100, 'is_active' => true])
            ->willReturn([
                'ok' => true,
                'status' => 200,
                'data' => [
                    ['id' => 7, 'code' => 'en', 'is_default' => false],
                    ['id' => 3, 'code' => 'es', 'is_default' => true],
                ],
            ]);

        $service = new LanguageApiService($mock);
        $this->assertSame(3, $service->defaultId());
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['id' => 'uuid-1']];

        $mock->expects($this->once())
            ->method('get')
            ->with('/cms/languages/uuid-1')
            ->willReturn($expected);

        $service = new LanguageApiService($mock);
        $this->assertSame($expected, $service->get('uuid-1'));
    }

    public function testCreateCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $payload  = ['name' => 'Test'];
        $expected = ['ok' => true, 'status' => 201, 'data' => ['id' => 'uuid-2']];

        $mock->expects($this->once())
            ->method('post')
            ->with('/cms/languages', $payload)
            ->willReturn($expected);

        $service = new LanguageApiService($mock);
        $this->assertSame($expected, $service->create($payload));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $mock     = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => []];

        $mock->expects($this->once())
            ->method('delete')
            ->with('/cms/languages/uuid-3')
            ->willReturn($expected);

        $service = new LanguageApiService($mock);
        $this->assertSame($expected, $service->delete('uuid-3'));
    }
}
