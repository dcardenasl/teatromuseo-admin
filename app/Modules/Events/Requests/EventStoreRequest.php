<?php

declare(strict_types=1);

namespace App\Modules\Events\Requests;

use App\Support\Requests\BaseFormRequest;

class EventStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['uuid', 'title', 'event_type', 'description', 'status'];
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|min_length[2]|max_length[255]',
            'title' => 'required|min_length[2]|max_length[255]',
            'event_type' => 'required|in_list[function,festival,course,workshop,other]',
            'description' => 'required|string',
            'status' => 'required|in_list[draft,published,cancelled]',
        ];
    }

    public function payload(): array
    {
        return [
            'uuid' => $this->postString('uuid'),
            'title' => $this->postString('title'),
            'event_type' => $this->postString('event_type'),
            'description' => $this->postString('description'),
            'status' => $this->postString('status'),
        ];
    }
}
