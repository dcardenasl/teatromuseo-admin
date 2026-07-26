<?php

declare(strict_types=1);

return [
    'sidebar_label'  => 'Site Identity',
    'page_title'     => 'Site Identity',
    'section_intro'  => 'Configure the site name, logo, tagline, and favicon.',
    'core_section'   => 'Core identity',
    'base_section_intro' => 'This value is the canonical source of truth reused across the system.',
    'base_badge'     => 'Base',
    'translatable_badge' => 'Translatable',
    'translations_section' => 'Translations',
    'translations_intro' => 'Use the tabs below to edit each localized version of the identity fields.',
    'translations_ready_suffix' => 'translatable fields',
    'translation_language_help' => 'Edit the localized values for this language. The base values are edited above.',
    'assets_section' => 'Brand assets',
    'update_success' => 'Site identity updated successfully.',
    'update_failed'  => 'Could not update site identity.',
    'cache_note'     => 'Changes appear on the public site after ~1 hour (cache). To see changes immediately, run <code>php spark cache:clear</code> in ci4-website-builder-web.',

    // Generic file labels (used for new metadata-driven fields)
    'select_file'  => 'Select file',
    'change_file'  => 'Change file',
    'remove_file'  => 'Remove',

    // Metadata-driven fallback when no settings exist
    'no_settings'  => 'No identity settings have been configured. Create settings with group "identity" to populate this page.',
];
