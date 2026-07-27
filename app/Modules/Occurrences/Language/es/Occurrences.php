<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de Occurrence',
    'list'           => 'Lista de occurrences',
    'create'         => 'Crear occurrence',
    'edit'           => 'Editar occurrence',
    'show'           => 'Detalle de occurrence',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este occurrence?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Ingresa Event ID',
        'event_id_help'        => 'Event aggregate that owns the scheduled session.',
        'venue_id'             => 'Venue ID',
        'venue_id_placeholder' => 'Ingresa Venue ID',
        'venue_id_help'        => 'Reusable venue assigned to this session.',
        'start_time'             => 'Start Time',
        'start_time_placeholder' => 'Ingresa Start Time',
        'start_time_help'        => 'Session start date and time.',
        'end_time'             => 'End Time',
        'end_time_placeholder' => 'Ingresa End Time',
        'end_time_help'        => 'Session end date and time.',
        'status'             => 'Status',
        'status_placeholder' => 'Ingresa Status',
        'status_help'        => 'Operational state of the scheduled session.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Ingresa Capacity',
        'capacity_help'        => 'Maximum capacity for this session.',
        'available_spots'             => 'Available Spots',
        'available_spots_placeholder' => 'Ingresa Available Spots',
        'available_spots_help'        => 'Remaining capacity for this session.',
    ],
];
