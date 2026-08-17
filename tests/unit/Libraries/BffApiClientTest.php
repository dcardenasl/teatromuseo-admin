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
    public function testValidDottedUrlWinsOverInvalidInjectedUppercaseValue(): void
    {
        $this->setEnvVar('bffApiClient.baseUrl', 'https://bff.teatromuseo.cl');
        $this->setEnvVar('BFF_API_BASE_URL', 'api');

        $config = new BffApiClientConfig();

        $this->assertSame('https://bff.teatromuseo.cl', $config->baseUrl);

        $this->unsetEnvVar('bffApiClient.baseUrl');
        $this->unsetEnvVar('BFF_API_BASE_URL');
    }

    public function testUppercaseUrlRemainsTheCompatibilityFallback(): void
    {
        $this->unsetEnvVar('bffApiClient.baseUrl');
        $this->setEnvVar('BFF_API_BASE_URL', 'https://bff.teatromuseo.cl');

        $config = new BffApiClientConfig();

        $this->assertSame('https://bff.teatromuseo.cl', $config->baseUrl);

        $this->unsetEnvVar('BFF_API_BASE_URL');
    }

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

    private function setEnvVar(string $key, string $value): void
    {
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }

    private function unsetEnvVar(string $key): void
    {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);
    }
}
