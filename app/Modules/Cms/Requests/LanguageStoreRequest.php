<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class LanguageStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['code', 'name', 'native_name', 'is_default', 'is_active', 'sort_order', 'fallback_language_id'];
    }

    public function rules(): array
    {
        return [
            'code' => 'required|min_length[2]|max_length[255]',
            'name' => 'required|min_length[2]|max_length[255]',
            'native_name' => 'permit_empty|string|max_length[255]',
            'is_default' => 'permit_empty|in_list[0,1]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'sort_order' => 'permit_empty|integer',
            'fallback_language_id' => 'permit_empty|integer',
        ];
    }

    public function payload(): array
    {
        $fallback = $this->postString('fallback_language_id');
        return [
            'code' => $this->postString('code'),
            'name' => $this->postString('name'),
            'native_name' => $this->postString('native_name'),
            'is_default' => $this->postBool('is_default') ? '1' : '0',
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'sort_order' => $this->postInt('sort_order', 0),
            'fallback_language_id' => $fallback !== '' ? (int) $fallback : null,
        ];
    }
}
