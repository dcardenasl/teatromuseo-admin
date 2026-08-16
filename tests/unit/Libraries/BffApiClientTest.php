<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\ApiClientInterface;
use App\Libraries\BffApiClient;
use App\Support\SessionKeys;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\BffApiClient as BffApiClientConfig;
use ReflectionClass;

/**
 * @internal
 */
final class BffApiClientTest extends CIUnitTestCase
{
    public function testAdminDashboardReadUsesAuthenticatedBffRouteAndRetryBudget(): void
    {
        session()->set(SessionKeys::ACCESS_TOKEN->value, 'admin-access-token');

        $config = new BffApiClientConfig();
        $config->baseUrl = 'http://localhost:8188';
        $hubClient = $this->createMock(ApiClientInterface::class);
        $client = new BffApiClient($config, $hubClient);
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn('{"data":{"sections":{}}}');

        $http = $this->createMock(CURLRequest::class);
        $http->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/api/v1/me/admin-dashboard',
                $this->callback(function (array $options): bool {
                    $this->assertSame('Bearer admin-access-token', $options['headers']['Authorization']);
                    $this->assertSame('application/json', $options['headers']['Accept']);
                    $this->assertArrayNotHasKey('max_retries', $options);

                    return true;
                })
            )
            ->willReturn($response);

        $this->setProtectedProperty($client, 'http', $http);

        $result = $client->getAdminDashboard(1);

        $this->assertTrue($result['ok']);
        $this->assertSame(200, $result['status']);
        $this->assertSame(['data' => ['sections' => []]], $result['data']);
    }

    private function setProtectedProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass($object);
        $reflection->getParentClass()?->getProperty($property)->setValue($object, $value);
    }
}
