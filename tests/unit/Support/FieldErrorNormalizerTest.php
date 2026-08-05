<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\FieldErrorNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FieldErrorNormalizerTest extends TestCase
{
    public function testNestedApiErrorsBecomeFormFieldKeys(): void
    {
        $errors = FieldErrorNormalizer::normalize([
            'translations' => [
                0 => ['excerpt' => ['The excerpt may not exceed 500 characters.']],
                1 => ['title' => 'The title is required.'],
            ],
            'general' => 'The request is invalid.',
        ]);

        $this->assertSame([
            'translations.0.excerpt' => 'The excerpt may not exceed 500 characters.',
            'translations.1.title' => 'The title is required.',
        ], $errors);
    }

    public function testFlatAndMessageWrappedErrorsKeepTheirFieldName(): void
    {
        $errors = FieldErrorNormalizer::normalize([
            'excerpt' => ['message' => 'Too long.'],
            'title' => ['Too short.'],
        ]);

        $this->assertSame([
            'excerpt' => 'Too long.',
            'title' => 'Too short.',
        ], $errors);
    }
}
