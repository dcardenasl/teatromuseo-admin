<?php

declare(strict_types=1);

namespace App\Modules\Events\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class EventTypeStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'is_active', 'translations'];
    }

    public function rules(): array
    {
        return [
            'name' => 'permit_empty|min_length[2]|max_length[255]',
            'is_active' => 'permit_empty',
            'translations' => 'required',
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $translations = $this->normalizeTranslations();
        $default = $this->resolveDefaultTranslation($translations);

        return [
            // Legacy projection only. Public slugs live in translations and
            // are synchronized by the event domain's slug store.
            'slug' => (string) ($default['slug'] ?? ''),
            'name' => $this->postString('name') !== '' ? $this->postString('name') : (string) ($default['name'] ?? ''),
            'is_active' => $this->postBool('is_active'),
            'translations' => $translations,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function normalizeTranslations(): array
    {
        return TranslationRowNormalizer::normalize(
            $this->postArray('translations'),
            static fn (array $row): bool => trim((string) ($row['name'] ?? '')) !== '',
            static function (array $row): array {
                $locale = trim((string) ($row['locale'] ?? $row['code'] ?? ''));
                $data = [
                    'locale' => $locale !== '' ? $locale : null,
                    'language_id' => isset($row['language_id']) && is_numeric($row['language_id']) ? (int) $row['language_id'] : null,
                    'name' => trim((string) ($row['name'] ?? '')),
                    'slug' => trim((string) ($row['slug'] ?? '')),
                ];

                return array_filter($data, static fn ($value): bool => $value !== null && $value !== '');
            }
        );
    }
}
