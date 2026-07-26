<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

interface BlockCatalogServiceInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function indexed(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function templates(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableForEntries(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableTopLevel(): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function selectableForPages(): array;
}
