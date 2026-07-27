<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de Booking',
    'list'           => 'Lista de bookings',
    'create'         => 'Crear booking',
    'edit'           => 'Editar booking',
    'show'           => 'Detalle de booking',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este booking?',
    'fields'         => [
        'uuid'             => 'UUID',
        'uuid_placeholder' => 'Ingresa UUID',
        'uuid_help'        => 'Stable identifier used by integrations.',
        'user_id'             => 'User ID',
        'user_id_placeholder' => 'Ingresa User ID',
        'user_id_help'        => 'Registered user who made the booking.',
        'guest_email'             => 'Guest Email',
        'guest_email_placeholder' => 'guest@example.com',
        'guest_email_help'        => 'Email for guests that check out without an account.',
        'total_amount'             => 'Total Amount',
        'total_amount_placeholder' => 'Ingresa Total Amount',
        'total_amount_help'        => 'Final amount charged for the booking.',
        'status'             => 'Status',
        'status_placeholder' => 'Ingresa Status',
        'status_help'        => 'Current booking lifecycle state.',
        'reserved_until'             => 'Reserved Until',
        'reserved_until_placeholder' => 'Ingresa Reserved Until',
        'reserved_until_help'        => 'Expiration timestamp for the booking reservation.',
    ],
];
