<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Services\ResourceApiService;

class LanguageApiService extends ResourceApiService
{
    protected function resourcePath(): string
    {
        return '/cms/languages';
    }

    public function defaultId(): int
    {
        $response = $this->list(['limit' => 100, 'is_active' => true]);
        $languages = $response['data'] ?? [];

        if (isset($languages['data']) && is_array($languages['data'])) {
            $languages = $languages['data'];
        }

        if (! is_array($languages)) {
            return 0;
        }

        foreach ($languages as $language) {
            if (! is_array($language)) {
                continue;
            }

            if (! empty($language['is_default']) && isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        foreach ($languages as $language) {
            if (is_array($language) && isset($language['id']) && is_numeric($language['id'])) {
                return (int) $language['id'];
            }
        }

        return 0;
    }

}
