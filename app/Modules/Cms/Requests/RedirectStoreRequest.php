<?php

declare(strict_types=1);

namespace App\Modules\Cms\Requests;

use App\Support\Requests\BaseFormRequest;

class RedirectStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['old_path', 'new_url', 'redirect_type', 'is_active', 'note'];
    }

    public function rules(): array
    {
        return [
            'old_path' => 'required|min_length[2]|max_length[255]|regex_match[/^\\/.*$/]',
            'new_url' => 'required|min_length[2]|max_length[255]',
            'redirect_type' => 'permit_empty|in_list[301,302]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'note' => 'permit_empty|string|max_length[255]',
        ];
    }

    public function payload(): array
    {
        return [
            'old_path' => $this->postString('old_path'),
            'new_url' => $this->postString('new_url'),
            'redirect_type' => $this->postInt('redirect_type', 301),
            'is_active' => $this->postBool('is_active') ? '1' : '0',
            'note' => $this->postString('note'),
        ];
    }
}
