<?php

declare(strict_types=1);

namespace App\Modules\Events\Requests;

use App\Support\Requests\BaseFormRequest;

class EventStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['title', 'event_type', 'description', 'cover_file_id', 'gallery_file_ids', 'status', 'translations'];
    }

    public function rules(): array
    {
        return [
            'title' => 'required|min_length[2]|max_length[255]',
            'event_type' => 'required|string|max_length[80]',
            'description' => 'required|string',
            'cover_file_id' => 'permit_empty',
            'gallery_file_ids' => 'permit_empty|string',
            'status' => 'required|in_list[draft,published,cancelled]',
            'translations' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'title' => $this->postString('title'),
            'event_type' => $this->postString('event_type'),
            'description' => $this->postString('description'),
            'cover_file_id' => $this->postNullableInt('cover_file_id'),
            'gallery_file_ids' => $this->postString('gallery_file_ids'),
            'status' => $this->postString('status'),
            'translations' => $this->normalizeTranslations(),
        ];
    }

    /**
     * Keep the cross-domain contract small and explicit. Locale is a code,
     * never the CMS database id, so Event remains independent from CMS storage.
     *
     * @return list<array{locale: string, title?: string, description?: string}>
     */
    private function normalizeTranslations(): array
    {
        $rawRows = $this->postArray('translations');
        $rows = [];

        foreach ($rawRows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $locale = isset($row['locale']) && is_scalar($row['locale'])
                ? trim((string) $row['locale'])
                : '';
            if ($locale === '') {
                continue;
            }

            $normalized = ['locale' => $locale];
            foreach (['title', 'description'] as $field) {
                if (isset($row[$field]) && is_scalar($row[$field])) {
                    $normalized[$field] = trim((string) $row[$field]);
                }
            }

            // Include empty locale rows on update: an empty tab is an explicit
            // request to remove that locale's persisted fields. Locales omitted
            // from the request (for example, inactive CMS languages) remain
            // untouched in the Event Domain.
            $rows[] = $normalized;
        }

        return $rows;
    }
}
