<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Keeps every Admin module that opts into the broad `AdminFilter` section
 * gate (`['auth', 'admin']`) from relying on it as the sole authorization —
 * each of its routes must also declare a fine-grained `permission:<code>`
 * filter, unless it documents an in-controller OR-of-permissions check
 * instead (see CONTROLLER_ENFORCED_ROUTES).
 *
 * The set of modules under test is discovered dynamically by scanning every
 * `app/Modules/{Module}/Config/Routes.php` for the `['auth', 'admin']`
 * pattern, rather than a hardcoded module list. This is deliberately narrower
 * than "every route in every module must have permission:" — modules such as
 * `Users`/`ApiKeys` (per-route permission under a plain `auth` group) or
 * `Analytics`/`Audit`/`Metrics` (a single `permission:<code>` on the whole
 * group) use different, already-reviewed authorization idioms that were
 * never vulnerable to the bug this test guards against (a module using the
 * broad any-of-18-read-permissions `AdminFilter` as its ONLY gate for CRUD).
 * Scoping by "does this module use the broad admin gate at all" means a
 * future module 9 that copies that exact mistake is caught automatically,
 * without this test needing to understand every other module's idiom.
 */
final class AdminRouteAuthorizationTest extends CIUnitTestCase
{
    /**
     * Routes that intentionally have no route-level `permission:` filter
     * because access requires an OR of multiple permission codes from
     * different domains, which the `permission:<code>` filter can't
     * express. Each one enforces its own OR-of-permissions check in the
     * controller instead. Keyed by module name.
     *
     * @var array<string, list<string>>
     */
    private const CONTROLLER_ENFORCED_ROUTES = [
        'Cms' => [
            "'wizard/structure'",
            "'wizard/structure/config'",
            "'wizard/structure/create-collection'",
            "'wizard/structure/create-page'",
            "'wizard/structure/create-menu'",
            "'translate'",
        ],
    ];

    private const BROAD_ADMIN_GATE_PATTERN = "/'filter'\\s*=>\\s*\\[\\s*'auth'\\s*,\\s*'admin'/";

    /** @return array<string, string> module name => route file relative path */
    private static function discoverModulesUsingBroadAdminGate(): array
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $modulesDir = $root . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules';
        $files = glob($modulesDir . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Routes.php') ?: [];
        sort($files);

        $modules = [];
        foreach ($files as $path) {
            $source = file_get_contents($path);
            if (! is_string($source) || ! preg_match(self::BROAD_ADMIN_GATE_PATTERN, $source)) {
                continue;
            }

            $relativePath = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $parts = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));
            $module = $parts[2] ?? '';
            if ($module !== '') {
                $modules[$module] = $relativePath;
            }
        }

        return $modules;
    }

    public function testModulesUsingBroadAdminGateDeclarePermissionOnEveryRoute(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $violations = [];

        foreach (self::discoverModulesUsingBroadAdminGate() as $module => $relativePath) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$relativePath}");
            if (! is_string($source)) {
                continue;
            }

            $exemptRoutes = self::CONTROLLER_ENFORCED_ROUTES[$module] ?? [];

            foreach (preg_split('/\\R/', $source) ?: [] as $lineNumber => $line) {
                if (! preg_match('/\$routes->(?:get|post)\\s*\\(/', $line)) {
                    continue;
                }

                if (str_contains($line, "'permission:")) {
                    continue;
                }

                $isExempt = false;
                foreach ($exemptRoutes as $exemptRoute) {
                    if (str_contains($line, $exemptRoute)) {
                        $isExempt = true;
                        break;
                    }
                }

                if (! $isExempt) {
                    $violations[] = sprintf('%s:%d has no explicit permission filter', $relativePath, $lineNumber + 1);
                }
            }
        }

        $this->assertSame([], $violations, implode("\n", $violations));
    }

    /**
     * The 8 domain modules that were found using the broad `AdminFilter`
     * gate as their ONLY authorization (2026-08-12 audit finding) must never
     * reintroduce it, regardless of whether they currently show up in
     * {@see discoverModulesUsingBroadAdminGate()} — this assertion stays
     * independent of that dynamic discovery so removing the pattern from a
     * module can never silently remove its own regression coverage.
     */
    public function testFormerlyVulnerableDomainModulesDoNotUseBroadAdminGate(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $routeFiles = [
            'app/Modules/Bookings/Config/Routes.php',
            'app/Modules/EventReferences/Config/Routes.php',
            'app/Modules/Events/Config/Routes.php',
            'app/Modules/Museum/Config/Routes.php',
            'app/Modules/Occurrences/Config/Routes.php',
            'app/Modules/TicketTypes/Config/Routes.php',
            'app/Modules/Tickets/Config/Routes.php',
            'app/Modules/Venues/Config/Routes.php',
        ];

        foreach ($routeFiles as $relativePath) {
            $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $source = file_get_contents($path);

            $this->assertIsString($source, "Unable to read {$relativePath}");
            if (! is_string($source)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                self::BROAD_ADMIN_GATE_PATTERN,
                $source,
                "{$relativePath} must not use the broad AdminFilter for CRUD authorization."
            );
        }
    }
}
