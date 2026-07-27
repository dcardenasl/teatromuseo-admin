<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de Ticket',
    'list'           => 'Lista de tickets',
    'create'         => 'Crear ticket',
    'edit'           => 'Editar ticket',
    'show'           => 'Detalle de ticket',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este ticket?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Ingresa UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'booking_id'             => 'Booking ID',
        'booking_id_placeholder' => 'Ingresa Booking ID',
        'booking_id_help'        => 'Booking that owns this ticket.',
        'ticket_type_id'             => 'Ticket Type ID',
        'ticket_type_id_placeholder' => 'Ingresa Ticket Type ID',
        'ticket_type_id_help'        => 'Ticket type assigned to the holder.',
        'holder_name'             => 'Holder Name',
        'holder_name_placeholder' => 'Jane Doe',
        'holder_name_help'        => 'Name printed on the ticket.',
        'holder_email'             => 'Holder Email',
        'holder_email_placeholder' => 'jane@example.com',
        'holder_email_help'        => 'Email attached to the ticket.',
        'qr_code_token'             => 'QR Code Token',
        'qr_code_token_placeholder' => 'Ingresa QR Code Token',
        'qr_code_token_help'        => 'Token encoded in the QR code.',
        'status'             => 'Status',
        'status_placeholder' => 'Ingresa Status',
        'status_help'        => 'Ticket validity or usage state.',
        'checked_in_at'             => 'Checked In At',
        'checked_in_at_placeholder' => 'Ingresa Checked In At',
        'checked_in_at_help'        => 'When the attendee checked in.',
    ],
];
