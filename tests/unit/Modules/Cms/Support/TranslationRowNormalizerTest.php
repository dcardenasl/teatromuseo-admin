<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Cms\Support;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TranslationRowNormalizerTest extends CIUnitTestCase
{
    public function testNormalizeDropsEmptyRowsAndKeepsMeaningfulRows(): void
    {
        $rows = [
            [
                'language_id' => '1',
                'name' => 'Hola',
                'slug' => 'hola',
                'description' => '',
            ],
            [
                'language_id' => '2',
                'name' => '',
                'slug' => '',
                'description' => '',
            ],
        ];

        $normalized = TranslationRowNormalizer::normalize(
            $rows,
            static function (array $row): bool {
                return trim((string) ($row['name'] ?? '')) !== '' || trim((string) ($row['slug'] ?? '')) !== '';
            },
            static function (array $row): array {
                return [
                    'language_id' => (int) ($row['language_id'] ?? 0),
                    'name' => trim((string) ($row['name'] ?? '')),
                    'slug' => trim((string) ($row['slug'] ?? '')),
                ];
            }
        );

        $this->assertSame([
            [
                'language_id' => 1,
                'name' => 'Hola',
                'slug' => 'hola',
            ],
        ], $normalized);
    }

    public function testNormalizeSupportsAssociativeLanguageKeys(): void
    {
        $rows = [
            1 => ['label' => 'Inicio', 'custom_url' => ''],
            2 => ['label' => '', 'custom_url' => ''],
            3 => ['label' => '', 'custom_url' => '/contacto'],
        ];

        $normalized = TranslationRowNormalizer::normalize(
            $rows,
            static function (array $row): bool {
                return trim((string) ($row['label'] ?? '')) !== '' || trim((string) ($row['custom_url'] ?? '')) !== '';
            },
            static function (array $row, int|string $languageId): array {
                return [
                    'language_id' => (int) $languageId,
                    'label' => trim((string) ($row['label'] ?? '')),
                    'custom_url' => trim((string) ($row['custom_url'] ?? '')) ?: null,
                ];
            }
        );

        $this->assertSame([
            [
                'language_id' => 1,
                'label' => 'Inicio',
                'custom_url' => null,
            ],
            [
                'language_id' => 3,
                'label' => '',
                'custom_url' => '/contacto',
            ],
        ], $normalized);
    }
}
