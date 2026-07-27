<?php

declare(strict_types=1);

return [
    'title'          => 'Occurrence Management',
    'list'           => 'Occurrence list',
    'create'         => 'Create occurrence',
    'edit'           => 'Edit occurrence',
    'show'           => 'Occurrence details',
    'delete_confirm' => 'Are you sure you want to delete this occurrence?',
    'fields'         => [
        'event_id'             => 'Event ID',
        'event_id_placeholder' => 'Enter Event ID',
        'event_id_help'        => 'Event aggregate that owns the scheduled session.',
        'venue_id'             => 'Venue ID',
        'venue_id_placeholder' => 'Enter Venue ID',
        'venue_id_help'        => 'Reusable venue assigned to this session.',
        'start_time'             => 'Start Time',
        'start_time_placeholder' => 'Enter Start Time',
        'start_time_help'        => 'Session start date and time.',
        'end_time'             => 'End Time',
        'end_time_placeholder' => 'Enter End Time',
        'end_time_help'        => 'Session end date and time.',
        'status'             => 'Status',
        'status_placeholder' => 'Enter Status',
        'status_help'        => 'Operational state of the scheduled session.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Enter Capacity',
        'capacity_help'        => 'Maximum capacity for this session.',
        'available_spots'             => 'Available Spots',
        'available_spots_placeholder' => 'Enter Available Spots',
        'available_spots_help'        => 'Remaining capacity for this session.',
    ],
];
