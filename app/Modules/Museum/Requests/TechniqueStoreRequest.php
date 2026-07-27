<?php

declare(strict_types=1);

namespace App\Modules\Museum\Requests;

use App\Support\Requests\BaseFormRequest;

class TechniqueStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['name', 'slug', 'summary', 'video_url', 'pdf_file_id', 'sort_order'];
    }

    public function rules(): array
    {
        return [
            'name' => 'required|min_length[2]|max_length[255]',
            'slug' => 'required|min_length[2]|max_length[255]',
            'summary' => 'permit_empty|string',
            'video_url' => 'permit_empty|string|max_length[255]',
            'pdf_file_id' => 'permit_empty|integer',
            'sort_order' => 'permit_empty|integer',
        ];
    }

    public function payload(): array
    {
        return [
            'name' => $this->postString('name'),
            'slug' => $this->postString('slug'),
            'summary' => $this->postString('summary'),
            'video_url' => $this->postString('video_url'),
            'pdf_file_id' => $this->postInt('pdf_file_id'),
            'sort_order' => $this->postInt('sort_order', 0),
        ];
    }
}
