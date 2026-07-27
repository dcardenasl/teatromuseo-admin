<?php

declare(strict_types=1);

return [
    'title'          => 'Venue Management',
    'list'           => 'Venue list',
    'create'         => 'Create venue',
    'edit'           => 'Edit venue',
    'show'           => 'Venue details',
    'delete_confirm' => 'Are you sure you want to delete this venue?',
    'fields'         => [
        'name'             => 'Name',
        'name_placeholder' => 'Enter Name',
        'name_help'        => 'Operational name of the venue.',
        'slug'             => 'Slug',
        'slug_placeholder' => 'Enter Slug',
        'slug_help'        => 'Stable URL-safe identifier.',
        'description'             => 'Description',
        'description_placeholder' => 'Enter Description',
        'description_help'        => 'Operational notes about the venue.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Enter Capacity',
        'capacity_help'        => 'Default capacity of the venue.',
        'is_active'             => 'Active',
        'is_active_placeholder' => 'Toggle Active',
        'is_active_help'        => 'Whether the venue can be selected for new occurrences.',
    ],
];
