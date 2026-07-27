<?php

declare(strict_types=1);

namespace App\Modules\Occurrences\Requests;

use App\Support\Requests\BaseFormRequest;

class OccurrenceStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['event_id', 'venue_id', 'start_time', 'end_time', 'status', 'capacity', 'available_spots'];
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'venue_id' => 'permit_empty',
            'start_time' => 'required|valid_date',
            'end_time' => 'required|valid_date',
            'status' => 'required|min_length[2]|max_length[255]',
            'capacity' => 'required|integer',
            'available_spots' => 'required|integer',
        ];
    }

    public function payload(): array
    {
        return [
            'event_id' => $this->postInt('event_id'),
            'venue_id' => $this->postInt('venue_id'),
            'start_time' => $this->postString('start_time'),
            'end_time' => $this->postString('end_time'),
            'status' => $this->postString('status'),
            'capacity' => $this->postInt('capacity'),
            'available_spots' => $this->postInt('available_spots'),
        ];
    }
}
