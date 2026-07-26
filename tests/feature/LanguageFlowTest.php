<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class LanguageFlowTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testSetLocalePersistsSupportedLocaleInSession(): void
    {
        $supported = config('App')->supportedLocales;
        $locale = $supported[0];

        $result = $this->withHeaders([
            'Referer' => site_url('login'),
            'X-CSRF-TOKEN' => csrf_hash(),
        ])->post('/language/set', ['locale' => $locale]);

        $result->assertRedirect();
        $result->assertSessionHas('locale', $locale);
    }

    public function testUnsupportedLocaleDoesNotOverwriteSession(): void
    {
        $supported = config('App')->supportedLocales;
        $currentLocale = $supported[0];
        $unsupportedLocale = 'fixture-unsupported-locale';
        while (in_array($unsupportedLocale, $supported, true)) {
            $unsupportedLocale .= '-next';
        }

        $result = $this->withSession([
            'locale' => $currentLocale,
        ])->withHeaders([
            'Referer' => site_url('login'),
            'X-CSRF-TOKEN' => csrf_hash(),
        ])->post('/language/set', ['locale' => $unsupportedLocale]);

        $result->assertRedirect();
        $result->assertSessionHas('locale', $currentLocale);
    }
}
