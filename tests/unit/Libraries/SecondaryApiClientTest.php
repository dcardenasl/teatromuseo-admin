<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\ApiClientInterface;
use App\Libraries\BffApiClient;
use App\Libraries\DomainApiClient;
use App\Libraries\SecondaryApiClient;
use CodeIgniter\Test\CIUnitTestCase;
use Config\ApiClient as ApiClientConfig;
use Config\BffApiClient as BffApiClientConfig;
use Config\DomainApiClient as DomainApiClientConfig;

/**
 * Covers the fix for domain/BFF clients wiping the session by trying to
 * refresh JWTs against their own host — only the Hub issues/refreshes
 * tokens, so {@see SecondaryApiClient::attemptTokenRefresh()} must always
 * delegate to it instead of the inherited `ApiClient` behaviour.
 *
 * @internal
 */
final class SecondaryApiClientTest extends CIUnitTestCase
{
    public function testDomainApiClientIsASecondaryApiClient(): void
    {
        $this->assertTrue(is_subclass_of(DomainApiClient::class, SecondaryApiClient::class));
    }

    public function testBffApiClientIsASecondaryApiClient(): void
    {
        $this->assertTrue(is_subclass_of(BffApiClient::class, SecondaryApiClient::class));
    }

    public function testDomainApiClientDelegatesTokenRefreshToHubClient(): void
    {
        $hubClient = $this->createMock(ApiClientInterface::class);
        $hubClient->expects($this->once())
            ->method('attemptTokenRefresh')
            ->willReturn(true);

        $client = new DomainApiClient(new DomainApiClientConfig(), $hubClient);

        $this->assertTrue($client->attemptTokenRefresh());
    }

    public function testDomainApiClientPropagatesHubRefreshFailure(): void
    {
        $hubClient = $this->createMock(ApiClientInterface::class);
        $hubClient->method('attemptTokenRefresh')->willReturn(false);

        $client = new DomainApiClient(new DomainApiClientConfig(), $hubClient);

        $this->assertFalse($client->attemptTokenRefresh());
    }

    public function testBffApiClientDelegatesTokenRefreshToHubClient(): void
    {
        $hubClient = $this->createMock(ApiClientInterface::class);
        $hubClient->expects($this->once())
            ->method('attemptTokenRefresh')
            ->willReturn(true);

        $client = new BffApiClient(new BffApiClientConfig(), $hubClient);

        $this->assertTrue($client->attemptTokenRefresh());
    }

    public function testAnonymousSecondaryClientNeverCallsItsOwnHost(): void
    {
        $hubClient = $this->createMock(ApiClientInterface::class);
        $hubClient->expects($this->once())
            ->method('attemptTokenRefresh')
            ->willReturn(true);

        $client = new class (new ApiClientConfig(), $hubClient) extends SecondaryApiClient {
        };

        $this->assertTrue($client->attemptTokenRefresh());
    }
}
