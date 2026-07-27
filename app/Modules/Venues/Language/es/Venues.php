<?php

declare(strict_types=1);

return [
    'title'          => 'Gestión de Venue',
    'list'           => 'Lista de venues',
    'create'         => 'Crear venue',
    'edit'           => 'Editar venue',
    'show'           => 'Detalle de venue',
    'delete_confirm' => '¿Estás seguro de que deseas eliminar este venue?',
    'fields'         => [
        'name'             => 'Name',
        'name_placeholder' => 'Ingresa Name',
        'name_help'        => 'Operational name of the venue.',
        'slug'             => 'Slug',
        'slug_placeholder' => 'Ingresa Slug',
        'slug_help'        => 'Stable URL-safe identifier.',
        'description'             => 'Description',
        'description_placeholder' => 'Ingresa Description',
        'description_help'        => 'Operational notes about the venue.',
        'capacity'             => 'Capacity',
        'capacity_placeholder' => 'Ingresa Capacity',
        'capacity_help'        => 'Default capacity of the venue.',
        'is_active'             => 'Active',
        'is_active_placeholder' => 'Activa o desactiva Active',
        'is_active_help'        => 'Whether the venue can be selected for new occurrences.',
    ],
];
