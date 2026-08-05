<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Converts the different validation error shapes used by CI4 and the API into
 * the dot-notated keys consumed by the form helpers.
 */
final class FieldErrorNormalizer
{
    /**
     * @param mixed $errors
     * @return array<string, string>
     */
    public static function normalize(mixed $errors): array
    {
        if (! is_array($errors)) {
            return [];
        }

        $normalized = [];

        foreach ($errors as $key => $value) {
            if (! is_string($key) || $key === 'general') {
                continue;
            }

            self::flatten($value, $key, $normalized);
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @param array<string, string> $normalized
     */
    private static function flatten(mixed $value, string $path, array &$normalized): void
    {
        if (is_scalar($value)) {
            if ($path !== '' && ! isset($normalized[$path])) {
                $normalized[$path] = (string) $value;
            }

            return;
        }

        if (! is_array($value) || $value === []) {
            return;
        }

        $isList = array_is_list($value);
        $hasNestedArray = false;

        foreach ($value as $key => $entry) {
            if (is_array($entry)) {
                $hasNestedArray = true;
                break;
            }
        }

        // CI4 commonly represents one field error as ['field' => ['message']].
        // Do not expose the numeric message index as part of the field name.
        if ($isList && ! $hasNestedArray) {
            foreach ($value as $entry) {
                if (is_scalar($entry) && ! isset($normalized[$path])) {
                    $normalized[$path] = (string) $entry;
                    return;
                }
            }
        }

        $pathSegments = explode('.', $path);
        $parentIsIndexedRow = $pathSegments !== [] && ctype_digit((string) end($pathSegments));

        foreach ($value as $key => $entry) {
            if (! $parentIsIndexedRow && is_string($key) && in_array($key, ['message', 'detail', 'title', 'error'], true)) {
                if (is_scalar($entry) && $path !== '' && ! isset($normalized[$path])) {
                    $normalized[$path] = (string) $entry;
                }

                continue;
            }

            $childPath = $path === '' ? (string) $key : $path . '.' . $key;
            self::flatten($entry, $childPath, $normalized);
        }
    }
}
