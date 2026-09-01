<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guardrail to ensure Controllers delegate to the Service layer rather than calling
 * Api Clients directly.
 */
final class ControllerServiceDependencyTest extends CIUnitTestCase
{
    /**
     * Allowed exceptions for direct ApiClient / DomainApiClient usage.
     *
     * @var list<string>
     */
    private const ALLOWED_DIRECT_API_ACCESS = [
        'app/Controllers/BaseWebController.php',                      // Infrastructure base controller initialization
        'app/Modules/Auth/Controllers/AuthController.php',            // Auth controller calls apiClient->clearSessionAuth() for logout
        'app/Modules/Cms/Controllers/WizardController.php',            // WizardController calls domainApiClient directly for schema configuration
    ];

    /** @var array<string, string> */
    private const FORBIDDEN_PATTERNS = [
        'service_api_client' => '/\bservice\s*\(\s*[\'"](?:domainA|a)piClient[\'"]\s*\)/',
        'services_api_client' => '/\bServices\s*::\s*(?:domainA|a)piClient\s*\(/',
    ];

    public function testControllersDoNotUseApiClientDirectly(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $appDir = $root . DIRECTORY_SEPARATOR . 'app';

        $violations = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appDir));

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile() || !str_ends_with($file->getFilename(), '.php')) {
                continue;
            }

            $path = $file->getPathname();
            $relative = str_replace('\\', '/', ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR));

            // Only check Controllers
            if (!str_contains($relative, '/Controllers/')) {
                continue;
            }

            // Skip allowed whitelist
            if (in_array($relative, self::ALLOWED_DIRECT_API_ACCESS, true)) {
                continue;
            }

            $source = file_get_contents($path);
            if (!is_string($source) || $source === '') {
                continue;
            }

            // Strip comments and string literals
            $code = '';
            foreach (token_get_all($source) as $token) {
                if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING], true)) {
                    $code .= str_repeat("\n", substr_count($token[1], "\n"));
                    continue;
                }
                $code .= is_array($token) ? $token[1] : $token;
            }

            foreach (self::FORBIDDEN_PATTERNS as $ruleName => $pattern) {
                $count = preg_match_all($pattern, $code);
                if ($count > 0) {
                    $violations[] = "{$relative}: violating '{$ruleName}' (matched {$count} times)";
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Controller-Service separation violations found in teatromuseo-admin:\n- " . implode("\n- ", $violations) . "\n\n" .
            "Controllers must delegate external API calls to the Service layer rather than using the API clients directly."
        );
    }
}
