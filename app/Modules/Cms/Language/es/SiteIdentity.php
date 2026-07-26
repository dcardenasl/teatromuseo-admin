<?php

declare(strict_types=1);

return [
    'sidebar_label'  => 'Identidad del sitio',
    'page_title'     => 'Identidad del sitio',
    'section_intro'  => 'Configura el nombre, logo, tagline y favicon del sitio.',
    'core_section'   => 'Identidad principal',
    'base_section_intro' => 'Este valor es la fuente canónica que se reutiliza en todo el sistema.',
    'base_badge'     => 'Base',
    'translatable_badge' => 'Traducible',
    'translations_section' => 'Traducciones',
    'translations_intro' => 'Usa las pestañas de abajo para editar cada versión localizada de los campos de identidad.',
    'translations_ready_suffix' => 'campos listos',
    'translation_language_help' => 'Edita los valores localizados para este idioma. Los valores base se editan arriba.',
    'assets_section' => 'Activos de marca',
    'update_success' => 'Identidad del sitio actualizada correctamente.',
    'update_failed'  => 'No se pudo actualizar la identidad del sitio.',
    'cache_note'     => 'Los cambios se reflejan en el sitio público después de ~1 hora (caché). Para ver los cambios de inmediato, ejecuta <code>php spark cache:clear</code> en ci4-website-builder-web.',

    // Etiquetas genéricas para campos de archivo metadata-driven
    'select_file'  => 'Seleccionar archivo',
    'change_file'  => 'Cambiar archivo',
    'remove_file'  => 'Quitar',

    // Estado vacío cuando no hay settings de identidad
    'no_settings'  => 'No se han configurado settings de identidad. Crea settings con el grupo "identity" para poblar esta página.',
];
