<?php

declare(strict_types=1);

return [
    'title'          => 'Ticket Management',
    'list'           => 'Ticket list',
    'create'         => 'Create ticket',
    'edit'           => 'Edit ticket',
    'show'           => 'Ticket details',
    'delete_confirm' => 'Are you sure you want to delete this ticket?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Enter UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'booking_id'             => 'Booking ID',
        'booking_id_placeholder' => 'Enter Booking ID',
        'booking_id_help'        => 'Booking that owns this ticket.',
        'ticket_type_id'             => 'Ticket Type ID',
        'ticket_type_id_placeholder' => 'Enter Ticket Type ID',
        'ticket_type_id_help'        => 'Ticket type assigned to the holder.',
        'holder_name'             => 'Holder Name',
        'holder_name_placeholder' => 'Jane Doe',
        'holder_name_help'        => 'Name printed on the ticket.',
        'holder_email'             => 'Holder Email',
        'holder_email_placeholder' => 'jane@example.com',
        'holder_email_help'        => 'Email attached to the ticket.',
        'qr_code_token'             => 'QR Code Token',
        'qr_code_token_placeholder' => 'Enter QR Code Token',
        'qr_code_token_help'        => 'Token encoded in the QR code.',
        'status'             => 'Status',
        'status_placeholder' => 'Enter Status',
        'status_help'        => 'Ticket validity or usage state.',
        'checked_in_at'             => 'Checked In At',
        'checked_in_at_placeholder' => 'Enter Checked In At',
        'checked_in_at_help'        => 'When the attendee checked in.',
    ],
];
