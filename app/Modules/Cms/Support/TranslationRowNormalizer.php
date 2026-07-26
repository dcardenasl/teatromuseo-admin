<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

/**
 * Shared helper for multilingual form rows.
 *
 * The admin uses a few different translation payload shapes, but they all need
 * the same top-level behavior:
 *   - ignore non-array rows,
 *   - ignore rows without content,
 *   - preserve row order,
 *   - return a compact list for the API.
 *
 * @internal
 */
final class TranslationRowNormalizer
{
    /**
     * Normalize a raw translation payload into a compact list.
     *
     * @param mixed $rawRows
     * @param callable(array<string, mixed>, int|string): bool $isMeaningful
     * @param callable(array<string, mixed>, int|string): (array<string, mixed>|null) $mapRow
     *
     * @return array<int, array<string, mixed>>
     */
    public static function normalize(mixed $rawRows, callable $isMeaningful, callable $mapRow): array
    {
        if (! is_array($rawRows)) {
            return [];
        }

        $normalized = [];

        foreach ($rawRows as $key => $row) {
            if (! is_array($row) || ! $isMeaningful($row, $key)) {
                continue;
            }

            $mapped = $mapRow($row, $key);
            if (! is_array($mapped) || $mapped === []) {
                continue;
            }

            $normalized[] = $mapped;
        }

        return $normalized;
    }
}
