<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class CategoryStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'slug', 'icon', 'short_description', 'sort_order', 'translations'];
    }

    public function rules(): array
    {
        return [
            'name' => 'permit_empty|min_length[2]|max_length[255]',
            'slug' => 'required|min_length[2]|max_length[255]',
            'icon' => 'permit_empty|string|max_length[255]',
            'short_description' => 'permit_empty|string',
            'sort_order' => 'permit_empty|integer',
            'translations' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        $translations = $this->normalizeTranslations();
        $defaultTrans = $this->resolveDefaultTranslation($translations);

        $name = $this->postString('name') !== '' ? $this->postString('name') : ($defaultTrans['name'] ?? '');

        return [
            'name' => $name,
            'slug' => $this->postString('slug'),
            'icon' => $this->postString('icon'),
            'short_description' => $this->postString('short_description') !== '' ? $this->postString('short_description') : ($defaultTrans['short_description'] ?? null),
            'sort_order' => $this->postInt('sort_order', 0),
            'translations' => $translations,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTranslations(): array
    {
        return TranslationRowNormalizer::normalize(
            $this->request->getPost('translations'),
            static function (array $row): bool {
                $name = isset($row['name']) ? trim((string) $row['name']) : '';
                $shortDescription = isset($row['short_description']) ? trim((string) $row['short_description']) : '';

                return $name !== '' || $shortDescription !== '';
            },
            static function (array $row): array {
                $locale = trim((string) ($row['locale'] ?? $row['code'] ?? $row['language_code'] ?? ''));

                $data = [
                    'locale' => $locale !== '' ? $locale : null,
                    'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                    'short_description' => isset($row['short_description']) ? trim((string) $row['short_description']) : null,
                ];

                return array_filter($data, static fn ($v) => $v !== null && $v !== '');
            }
        );
    }
}
