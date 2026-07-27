<?php

declare(strict_types=1);

namespace App\Modules\Tickets\Requests;

use App\Support\Requests\BaseFormRequest;

class TicketStoreRequest extends BaseFormRequest
{
    protected function fields(): array
    {
        return ['uuid', 'booking_id', 'ticket_type_id', 'holder_name', 'holder_email', 'qr_code_token', 'status', 'checked_in_at'];
    }

    public function rules(): array
    {
        return [
            'uuid' => 'permit_empty|string|max_length[255]',
            'booking_id' => 'required',
            'ticket_type_id' => 'required',
            'holder_name' => 'required|min_length[2]|max_length[255]',
            'holder_email' => 'required|min_length[2]|max_length[255]',
            'qr_code_token' => 'permit_empty|string|max_length[255]',
            'status' => 'required|in_list[valid,used,void]',
            'checked_in_at' => 'permit_empty|valid_date',
        ];
    }

    public function payload(): array
    {
        return [
            'booking_id' => $this->postInt('booking_id'),
            'ticket_type_id' => $this->postInt('ticket_type_id'),
            'holder_name' => $this->postString('holder_name'),
            'holder_email' => $this->postString('holder_email'),
            'status' => $this->postString('status'),
            'checked_in_at' => $this->postString('checked_in_at') ?: null,
        ];
    }
}
