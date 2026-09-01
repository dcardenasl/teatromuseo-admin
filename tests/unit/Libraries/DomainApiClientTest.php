<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\ApiClient;
use App\Libraries\ApiClientInterface;
use App\Libraries\DomainApiClient;
use App\Libraries\DomainApiClientInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\CatalogDomainApiClient as CatalogDomainApiClientConfig;
use Config\DomainApiClient as DomainApiClientConfig;
use Config\EventDomainApiClient as EventDomainApiClientConfig;
use Config\Services;

/**
 * @internal
 */
final class DomainApiClientTest extends CIUnitTestCase
{
    public function testClassImplementsBothInterfaces(): void
    {
        $reflection = new \ReflectionClass(DomainApiClient::class);
        $this->assertTrue($reflection->implementsInterface(DomainApiClientInterface::class));
        $this->assertTrue($reflection->implementsInterface(ApiClientInterface::class));
    }

    public function testExtendsApiClient(): void
    {
        $this->assertTrue(is_subclass_of(DomainApiClient::class, ApiClient::class));
    }

    public function testDomainInterfaceExtendsApiInterface(): void
    {
        $reflection = new \ReflectionClass(DomainApiClientInterface::class);
        $this->assertTrue($reflection->isSubclassOf(ApiClientInterface::class));
    }

    public function testConfigDefaultBaseUrlPointsToPort8190(): void
    {
        $this->unsetEnvVar('domainApiClient.baseUrl');
        $this->unsetEnvVar('DOMAIN_API_BASE_URL');

        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:8190', $config->baseUrl);
    }

    public function testConfigInheritsApiClientDefaults(): void
    {
        $config = new DomainApiClientConfig();
        $this->assertSame(15, $config->timeout);
        $this->assertSame(5, $config->connectTimeout);
        $this->assertSame('/api/v1', $config->apiPrefix);
    }

    public function testConfigBaseUrlOverridableViaEnv(): void
    {
        $this->unsetEnvVar('domainApiClient.baseUrl');
        $this->setEnvVar('DOMAIN_API_BASE_URL', 'http://localhost:9999');

        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:9999', $config->baseUrl);

        $this->unsetEnvVar('DOMAIN_API_BASE_URL');
    }

    public function testConfigDottedKeyOverridesUppercase(): void
    {
        $this->setEnvVar('domainApiClient.baseUrl', 'http://dotted.example');
        $this->setEnvVar('DOMAIN_API_BASE_URL', 'http://uppercase.example');

        $config = new DomainApiClientConfig();
        $this->assertSame('http://dotted.example', $config->baseUrl);

        $this->unsetEnvVar('domainApiClient.baseUrl');
        $this->unsetEnvVar('DOMAIN_API_BASE_URL');
    }

    public function testCatalogConfigUsesItsDedicatedEnvironmentBaseUrl(): void
    {
        $this->unsetEnvVar('catalogDomainApiClient.baseUrl');
        $this->unsetEnvVar('CATALOG_DOMAIN_API_BASE_URL');
        $this->setEnvVar('CATALOG_DOMAIN_API_BASE_URL', 'https://catalog.example.test');

        $config = new CatalogDomainApiClientConfig();

        $this->assertSame('https://catalog.example.test', $config->baseUrl);

        $this->unsetEnvVar('CATALOG_DOMAIN_API_BASE_URL');
    }

    public function testEventConfigUsesItsDedicatedEnvironmentBaseUrl(): void
    {
        $this->unsetEnvVar('eventDomainApiClient.baseUrl');
        $this->unsetEnvVar('EVENT_DOMAIN_API_BASE_URL');
        $this->setEnvVar('EVENT_DOMAIN_API_BASE_URL', 'https://events.example.test');

        $config = new EventDomainApiClientConfig();

        $this->assertSame('https://events.example.test', $config->baseUrl);

        $this->unsetEnvVar('EVENT_DOMAIN_API_BASE_URL');
    }

    public function testSpecializedClientsDoNotReuseTheCmsDomainAppKey(): void
    {
        $this->unsetEnvVar('catalogDomainApiClient.appKey');
        $this->unsetEnvVar('CATALOG_DOMAIN_API_KEY');
        $this->setEnvVar('DOMAIN_API_APP_KEY', 'cms-domain-key');

        $catalogConfig = new CatalogDomainApiClientConfig();

        $this->assertSame('', $catalogConfig->appKey);

        $this->unsetEnvVar('DOMAIN_API_APP_KEY');
    }

    public function testConfigDoesNotReadApiClientHubEnvVars(): void
    {
        // Hub env vars must NOT leak into the domain config — otherwise both
        // clients would silently point at the same backend.
        $this->unsetEnvVar('domainApiClient.baseUrl');
        $this->unsetEnvVar('DOMAIN_API_BASE_URL');

        $this->setEnvVar('API_BASE_URL', 'http://hub.example');
        $this->setEnvVar('apiClient.baseUrl', 'http://hub.example');

        $config = new DomainApiClientConfig();
        $this->assertSame('http://localhost:8190', $config->baseUrl);

        $this->unsetEnvVar('API_BASE_URL');
        $this->unsetEnvVar('apiClient.baseUrl');
    }

    public function testServicesFactoryReturnsDomainApiClientInterface(): void
    {
        $instance = Services::domainApiClient(false);
        $this->assertInstanceOf(DomainApiClientInterface::class, $instance);
        $this->assertInstanceOf(DomainApiClient::class, $instance);
    }

    public function testServicesFactoryAndApiClientAreDistinct(): void
    {
        $hub    = Services::apiClient(false);
        $domain = Services::domainApiClient(false);

        $this->assertNotSame($hub, $domain);
        $this->assertInstanceOf(DomainApiClientInterface::class, $domain);
        // Domain client must satisfy ApiClientInterface so existing services accept it.
        $this->assertInstanceOf(ApiClientInterface::class, $domain);
    }

    public function testEventDomainFactoryUsesEventConfig(): void
    {
        $instance = Services::eventDomainApiClient(false);

        $this->assertInstanceOf(DomainApiClient::class, $instance);
        $config = $this->extractClientConfig($instance);

        $this->assertInstanceOf(EventDomainApiClientConfig::class, $config);
        $this->assertSame('http://localhost:8193', $config->baseUrl);
    }

    public function testCatalogDomainFactoryUsesCatalogConfig(): void
    {
        $instance = Services::catalogDomainApiClient(false);

        $this->assertInstanceOf(DomainApiClient::class, $instance);
        $config = $this->extractClientConfig($instance);

        $this->assertInstanceOf(CatalogDomainApiClientConfig::class, $config);
        $this->assertSame('http://localhost:8191', $config->baseUrl);
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

    /**
     * @return object
     */
    private function extractClientConfig(DomainApiClient $client): object
    {
        $reflection = new \ReflectionClass(ApiClient::class);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);

        $config = $property->getValue($client);

        $this->assertIsObject($config);

        return $config;
    }
}
