<?php

declare(strict_types=1);

return [
    // Index
    'title'    => 'Formularios Dinámicos',
    'subtitle' => 'Gestiona los formularios de contacto y consulta de tu sitio.',
    'empty'    => 'No hay formularios. Crea el primero.',
    'loading'  => 'Cargando formularios',
    'search_placeholder' => 'Buscar por clave o nombre...',

    // Columns
    'col_key'     => 'Clave',
    'col_name'    => 'Nombre',
    'col_captcha' => 'CAPTCHA',
    'col_fields'  => 'Campos',
    'col_active'  => 'Activo',
    'col_actions' => 'Acciones',

    // Badges
    'captcha_on' => 'Activo',
    'captcha_off' => 'Inactivo',

    // Page titles
    'create_title' => 'Nuevo Formulario',
    'edit_title'   => 'Editar Formulario',

    // Sections
    'section_general'      => 'General',
    'section_translations' => 'Traducciones',
    'section_fields'       => 'Constructor de Campos',

    // Form fields — general
    'field_key'           => 'Clave del Formulario',
    'field_key_hint'      => 'Slug único usado en plantillas (ej. contact). Alfanumérico, guiones y guiones bajos.',
    'field_key_readonly'  => 'La clave del formulario no puede cambiarse después de crearlo.',
    'field_active'        => 'Activo',
    'field_captcha'       => 'Requerir CAPTCHA',
    'field_captcha_hint'  => 'Activa la validación para este formulario. Las claves reCAPTCHA se gestionan en CMS > Configuración.',
    'field_notify_email'  => 'Email de Notificación',
    'field_notify_email_hint'       => 'El admin recibe un email por cada envío. Dejar vacío para desactivar.',
    'field_autoreply'               => 'Enviar respuesta automática al usuario',
    'field_autoreply_email_field'   => 'Clave del campo email',
    'field_autoreply_email_field_hint' => 'La field_key del campo email (ej. email). Se usa para enviar la respuesta automática.',

    // Form fields — translations
    'field_name'            => 'Nombre del Formulario',
    'field_description'     => 'Descripción',
    'field_submit_label'    => 'Texto del Botón Enviar',
    'field_success_message' => 'Mensaje de Éxito',
    'field_error_message'   => 'Mensaje de Error Genérico',

    // Field builder
    'fields_empty'   => 'No hay campos. Agrega el primero.',
    'btn_add_field'  => 'Agregar Campo',
    'required'       => 'Obligatorio',

    // Field modal
    'modal_create_field' => 'Agregar Campo',
    'modal_edit_field'   => 'Editar Campo',

    // Field form
    'field_field_key'    => 'Clave del Campo',
    'field_field_type'   => 'Tipo',
    'field_required'     => 'Obligatorio',
    'field_key_required' => 'La clave del campo es obligatoria.',
    'field_label'        => 'Etiqueta',
    'field_placeholder'  => 'Placeholder',
    'field_help_text'    => 'Texto de Ayuda',

    // Field types
    'field_type_text'     => 'Texto',
    'field_type_email'    => 'Email',
    'field_type_phone'    => 'Teléfono',
    'field_type_textarea' => 'Área de Texto',
    'field_type_select'   => 'Desplegable (select)',
    'field_type_radio'    => 'Opción única (radio)',
    'field_type_checkbox' => 'Opción múltiple (checkbox)',
    'field_type_date'     => 'Fecha',
    'field_type_number'   => 'Número',
    'field_type_url'      => 'URL',

    // Field options editor (select / radio / checkbox) — la lista de opciones
    // (agregar/quitar/valor) es estructural e independiente del idioma; la
    // etiqueta visible de cada opción es traducible y vive en la pestaña de
    // idioma de la derecha, junto a label/placeholder/help_text.
    'field_options'                  => 'Opciones',
    'field_options_structure_hint'   => 'Se muestran al visitante en el orden listado. Agrega o quita opciones aquí — escribe la etiqueta traducida de cada una en las pestañas de idioma de la derecha.',
    'field_option_labels'            => 'Etiquetas de opciones',
    'field_option_labels_hint'       => 'El texto que ve el visitante para cada opción, en este idioma. El valor que se guarda al enviar el formulario es el mismo en todos los idiomas.',
    'option_value'                   => 'Valor',
    'option_untitled'                => 'Falta la etiqueta — completala en las pestañas de idioma →',
    'btn_add_option'                 => 'Agregar opción',
    'btn_remove_option'              => 'Quitar',
    'btn_regenerate_option_value'    => 'Regenerar valor desde la etiqueta',
    'options_required'               => 'Agrega al menos una opción para este tipo de campo.',

    // Field-level auto-translate
    'btn_translate_field' => 'Traducir automáticamente este campo',

    // Buttons
    'btn_create' => 'Nuevo Formulario',
    'btn_save'   => 'Guardar',
    'btn_cancel' => 'Cancelar',
    'btn_edit'   => 'Editar',
    'btn_delete' => 'Eliminar',

    // Confirmations
    'confirm_delete'       => '¿Eliminar este formulario? Esta acción no se puede deshacer.',
    'confirm_delete_field' => '¿Eliminar este campo? Esta acción no se puede deshacer.',

    // Flash messages
    'create_success' => 'Formulario creado correctamente.',
    'create_failed'  => 'No se pudo crear el formulario.',
    'update_success' => 'Formulario actualizado correctamente.',
    'update_failed'  => 'No se pudo actualizar el formulario.',
    'delete_success' => 'Formulario eliminado correctamente.',
    'delete_failed'  => 'No se pudo eliminar el formulario.',
    'not_found'      => 'Formulario no encontrado.',
    'in_use_warning' => 'Este formulario está vinculado a {0} bloque(s). Elimina esos bloques antes de borrar el formulario.',

    // Field AJAX
    'save_field_failed' => 'No se pudo guardar el campo.',

    // Show details page
    'show_title'        => 'Detalle del Formulario',
    'translation_title' => 'Traducciones del Formulario',
    'fields_title'      => 'Campos del Formulario',
    'usages_title'      => 'Vínculos del Formulario',
    'usages_empty'      => 'Este formulario no está vinculado a ningún bloque de páginas o entradas.',
    'usage_page'        => 'Página',
    'usage_entry'       => 'Entrada',
    'usage_block'       => 'Bloque',
    'usage_instance'    => 'Instancia',
    'usage_edit'        => 'Abrir editor del bloque',
    'view_submissions'  => 'Ver Envíos',
];
