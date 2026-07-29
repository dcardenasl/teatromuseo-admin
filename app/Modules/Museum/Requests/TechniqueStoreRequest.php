<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class TechniqueStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'slug', 'summary', 'video_url', 'pdf_file_id', 'sort_order', 'translations'];
    }

    public function rules(): array
    {
        return [
            'name' => 'permit_empty|min_length[2]|max_length[255]',
            'slug' => 'required|min_length[2]|max_length[255]',
            'summary' => 'permit_empty|string',
            'video_url' => 'permit_empty|string|max_length[255]',
            'pdf_file_id' => 'permit_empty|integer',
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
            'summary' => $this->postString('summary') !== '' ? $this->postString('summary') : ($defaultTrans['summary'] ?? null),
            'video_url' => $this->postString('video_url'),
            'pdf_file_id' => $this->postNullableInt('pdf_file_id'),
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
                $summary = isset($row['summary']) ? trim((string) $row['summary']) : '';

                return $name !== '' || $summary !== '';
            },
            static function (array $row): array {
                $locale = trim((string) ($row['locale'] ?? $row['code'] ?? $row['language_code'] ?? ''));

                $data = [
                    'locale' => $locale !== '' ? $locale : null,
                    'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                    'summary' => isset($row['summary']) ? trim((string) $row['summary']) : null,
                ];

                return array_filter($data, static fn ($v) => $v !== null && $v !== '');
            }
        );
    }
}
