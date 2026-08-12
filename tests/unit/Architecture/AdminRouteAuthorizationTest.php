<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Keeps the Admin domain modules on the single authorization model:
 * authentication plus an explicit permission on every endpoint.
 */
final class AdminRouteAuthorizationTest extends CIUnitTestCase
{
    /** @var list<string> */
    private const ROUTE_FILES = [
        'app/Modules/Bookings/Config/Routes.php',
        'app/Modules/EventReferences/Config/Routes.php',
        'app/Modules/Events/Config/Routes.php',
        'app/Modules/Museum/Config/Routes.php',
        'app/Modules/Occurrences/Config/Routes.php',
        'app/Modules/TicketTypes/Config/Routes.php',
        'app/Modules/Tickets/Config/Routes.php',
        'app/Modules/Venues/Config/Routes.php',
    ];

    public function testEveryDomainEndpointDeclaresPermissionFilter(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $violations = [];

        foreach (self::ROUTE_FILES as $relativePath) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$relativePath}");
            if (! is_string($source)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                "/'filter'\\s*=>\\s*\\[\\s*'auth'\\s*,\\s*'admin'/",
                $source,
                "{$relativePath} must not use the broad AdminFilter for CRUD authorization."
            );

            foreach (preg_split('/\\R/', $source) ?: [] as $lineNumber => $line) {
                if (! preg_match('/\$routes->(?:get|post)\\s*\\(/', $line)) {
                    continue;
                }

                if (! str_contains($line, "'permission:")) {
                    $violations[] = sprintf('%s:%d has no explicit permission filter', $relativePath, $lineNumber + 1);
                }
            }
        }

        $this->assertSame([], $violations, implode("\n", $violations));
    }
}
