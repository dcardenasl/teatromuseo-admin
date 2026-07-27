<?php

declare(strict_types=1);

namespace App\Modules\EventReferences\Requests;

use App\Support\Requests\BaseFormRequest;

class EventReferenceStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['event_id', 'source_system', 'source_type', 'source_id', 'relation', 'metadata'];
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'source_system' => 'required|min_length[2]|max_length[255]',
            'source_type' => 'required|min_length[2]|max_length[255]',
            'source_id' => 'required|min_length[2]|max_length[255]',
            'relation' => 'required|min_length[2]|max_length[255]',
            'metadata' => 'permit_empty',
        ];
    }

    public function payload(): array
    {
        return [
            'event_id' => $this->postInt('event_id'),
            'source_system' => $this->postString('source_system'),
            'source_type' => $this->postString('source_type'),
            'source_id' => $this->postString('source_id'),
            'relation' => $this->postString('relation'),
            'metadata' => $this->postString('metadata'),
        ];
    }
}
