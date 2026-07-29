<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Modules\Cms\Support\TranslationRowNormalizer;
use App\Support\Requests\BaseFormRequest;

class CollectionItemStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return [
            'name', 'category_id', 'inventory_code', 'status', 'summary', 'curiosidad', 'contenido',
            'origin', 'period', 'creator', 'ubicacion', 'materials', 'cover_file_id', 'gallery_file_ids',
            'show_in_totem', 'internal_notes', 'collection_number', 'collection_group',
            'physical_description', 'dimensions', 'ingress_type', 'donated_by', 'tags', 'links',
            'company_history', 'is_active', 'translations',
        ];
    }

    public function rules(): array
    {
        return [
            'name' => 'permit_empty|min_length[2]|max_length[255]',
            'category_id' => 'required',
            'inventory_code' => 'required|min_length[2]|max_length[255]',
            'status' => 'permit_empty|min_length[2]|max_length[255]',
            'summary' => 'permit_empty|string',
            'curiosidad' => 'permit_empty|string',
            'contenido' => 'permit_empty|string',
            'origin' => 'permit_empty|string|max_length[255]',
            'period' => 'permit_empty|string|max_length[255]',
            'creator' => 'permit_empty|string|max_length[255]',
            'ubicacion' => 'permit_empty|string|max_length[255]',
            'materials' => 'permit_empty|string',
            'cover_file_id' => 'permit_empty',
            'gallery_file_ids' => 'permit_empty|string',
            'show_in_totem' => 'permit_empty',
            'internal_notes' => 'permit_empty|string',
            'collection_number' => 'permit_empty|string|max_length[255]',
            'collection_group' => 'permit_empty|string|max_length[255]',
            'physical_description' => 'permit_empty|string',
            'dimensions' => 'permit_empty|string|max_length[255]',
            'ingress_type' => 'permit_empty|string|max_length[255]',
            'donated_by' => 'permit_empty|string|max_length[255]',
            'tags' => 'permit_empty|string',
            'links' => 'permit_empty|string',
            'company_history' => 'permit_empty|string',
            'is_active' => 'permit_empty',
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
            'category_id' => $this->postInt('category_id'),
            'inventory_code' => $this->postString('inventory_code'),
            'status' => $this->postString('status') ?: 'published',
            'summary' => $this->postString('summary') !== '' ? $this->postString('summary') : ($defaultTrans['summary'] ?? null),
            'curiosidad' => $this->postString('curiosidad') !== '' ? $this->postString('curiosidad') : ($defaultTrans['curiosidad'] ?? null),
            'contenido' => $this->postString('contenido') !== '' ? $this->postString('contenido') : ($defaultTrans['contenido'] ?? null),
            'origin' => $this->postString('origin'),
            'period' => $this->postString('period'),
            'creator' => $this->postString('creator'),
            'ubicacion' => $this->postString('ubicacion') !== '' ? $this->postString('ubicacion') : ($defaultTrans['ubicacion'] ?? null),
            'materials' => $this->postString('materials'),
            'cover_file_id' => $this->postNullableInt('cover_file_id'),
            'gallery_file_ids' => $this->postString('gallery_file_ids'),
            'show_in_totem' => $this->postBool('show_in_totem') ? 1 : 0,
            'internal_notes' => $this->postString('internal_notes'),
            'collection_number' => $this->postString('collection_number'),
            'collection_group' => $this->postString('collection_group'),
            'physical_description' => $this->postString('physical_description') !== '' ? $this->postString('physical_description') : ($defaultTrans['physical_description'] ?? null),
            'dimensions' => $this->postString('dimensions'),
            'ingress_type' => $this->postString('ingress_type'),
            'donated_by' => $this->postString('donated_by'),
            'tags' => $this->postString('tags'),
            'links' => $this->postString('links'),
            'company_history' => $this->postString('company_history'),
            'is_active' => $this->postBool('is_active') ? 1 : 0,
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
                $contenido = isset($row['contenido']) ? trim((string) $row['contenido']) : '';
                $curiosidad = isset($row['curiosidad']) ? trim((string) $row['curiosidad']) : '';
                $physicalDescription = isset($row['physical_description']) ? trim((string) $row['physical_description']) : '';
                $ubicacion = isset($row['ubicacion']) ? trim((string) $row['ubicacion']) : '';

                return $name !== '' || $summary !== '' || $contenido !== '' || $curiosidad !== '' || $physicalDescription !== '' || $ubicacion !== '';
            },
            static function (array $row): array {
                $locale = trim((string) ($row['locale'] ?? $row['code'] ?? $row['language_code'] ?? ''));

                $data = [
                    'locale' => $locale !== '' ? $locale : null,
                    'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                    'summary' => isset($row['summary']) ? trim((string) $row['summary']) : null,
                    'curiosidad' => isset($row['curiosidad']) ? trim((string) $row['curiosidad']) : null,
                    'contenido' => isset($row['contenido']) ? trim((string) $row['contenido']) : null,
                    'physical_description' => isset($row['physical_description']) ? trim((string) $row['physical_description']) : null,
                    'ubicacion' => isset($row['ubicacion']) ? trim((string) $row['ubicacion']) : null,
                ];

                return array_filter($data, static fn ($v) => $v !== null && $v !== '');
            }
        );
    }
}
