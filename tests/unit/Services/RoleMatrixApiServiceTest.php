<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Libraries\ApiClientInterface;
use App\Modules\Iam\Services\RoleMatrixApiService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RoleMatrixApiServiceTest extends CIUnitTestCase
{
    public function testMatrixCallsCorrectEndpoint(): void
    {
        $mock = $this->createMock(ApiClientInterface::class);
        $expected = ['ok' => true, 'status' => 200, 'data' => ['data' => []]];

        $mock->expects($this->once())
            ->method('get')
            ->with('/api/v1/iam/role-permission-matrix')
            ->willReturn($expected);

        $service = new RoleMatrixApiService($mock);

        $this->assertSame($expected, $service->matrix());
    }
}
