<?php

declare(strict_types=1);

namespace App\Modules\Cms\Support;

final class CmsPresetCatalog
{
    /**
     * @return list<string>
     */
    public static function collectionTypes(): array
    {
        return ['blog', 'news', 'portfolio', 'services', 'other'];
    }

    /**
     * @return list<string>
     */
    public static function pageTypes(): array
    {
        return ['home', 'generic', 'contact', 'privacy', 'terms', '404', '500', 'maintenance', 'collection_index'];
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public static function collectionTypeOptions(): array
    {
        return array_map(
            static fn (string $type): array => ['key' => $type, 'label' => lang('Collections.collection_type_' . $type)],
            self::collectionTypes()
        );
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public static function pageTypeOptions(): array
    {
        return array_map(
            static fn (string $type): array => ['key' => $type, 'label' => lang('Pages.page_type_' . $type)],
            self::pageTypes()
        );
    }

    /**
     * @return array<int, array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: array<string, mixed>|null}>
     */
    public static function collectionPresets(): array
    {
        return [
            self::collectionPreset('blog', [
                ['block_key' => 'rich_text', 'label' => 'Introducción', 'help_text' => 'Primer bloque editorial', 'required' => true, 'locked' => false, 'block_config_defaults' => new \stdClass()],
                ['block_key' => 'image', 'label' => 'Imagen destacada', 'help_text' => 'Apoyo visual para la entrada', 'required' => false, 'locked' => false, 'block_config_defaults' => new \stdClass()],
            ], [
                ['step_title' => 'Detalles de la entrada', 'step_hint' => 'Define el nombre y un breve contexto', 'fields' => [
                    ['key' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
                    ['key' => 'excerpt', 'label' => 'Resumen', 'type' => 'textarea', 'required' => false],
                ]],
                ['step_title' => 'Imagen destacada', 'step_hint' => 'Portada de la entrada (biblioteca o URL)', 'fields' => [['key' => 'featured_image', 'label' => 'Imagen destacada', 'type' => 'image', 'required' => false]]],
            ]),
            self::collectionPreset('news', [
                ['block_key' => 'rich_text', 'label' => 'Titular', 'help_text' => 'Bloque principal de la noticia', 'required' => true, 'locked' => true, 'block_config_defaults' => new \stdClass()],
                ['block_key' => 'image', 'label' => 'Imagen de portada', 'help_text' => 'Acompaña la noticia con una imagen', 'required' => false, 'locked' => false, 'block_config_defaults' => new \stdClass()],
            ], [
                ['step_title' => 'Titular y resumen', 'step_hint' => 'Título visible y una breve bajada informativa', 'fields' => [
                    ['key' => 'title', 'label' => 'Titular', 'type' => 'text', 'required' => true],
                    ['key' => 'excerpt', 'label' => 'Resumen', 'type' => 'textarea', 'required' => false],
                ]],
                ['step_title' => 'Imagen destacada', 'step_hint' => 'Portada de la noticia (biblioteca o URL)', 'fields' => [['key' => 'featured_image', 'label' => 'Imagen destacada', 'type' => 'image', 'required' => false]]],
            ]),
            self::collectionPreset('portfolio', [
                ['block_key' => 'image', 'label' => 'Imagen del Proyecto', 'help_text' => 'Imagen principal del proyecto realizado', 'required' => true, 'locked' => false, 'block_config_defaults' => new \stdClass()],
                ['block_key' => 'rich_text', 'label' => 'Detalle del Proyecto', 'help_text' => 'Descripción detallada del caso de estudio', 'required' => false, 'locked' => false, 'block_config_defaults' => new \stdClass()],
            ], [
                ['step_title' => 'Proyecto y resumen', 'step_hint' => 'Nombre del proyecto y una breve descripción del trabajo realizado', 'fields' => [
                    ['key' => 'title', 'label' => 'Proyecto', 'type' => 'text', 'required' => true],
                    ['key' => 'excerpt', 'label' => 'Resumen', 'type' => 'textarea', 'required' => false],
                ]],
                ['step_title' => 'Imagen destacada', 'step_hint' => 'Portada del proyecto (biblioteca o URL)', 'fields' => [['key' => 'featured_image', 'label' => 'Imagen destacada', 'type' => 'image', 'required' => false]]],
            ]),
            self::collectionPreset('services', [
                ['block_key' => 'rich_text', 'label' => 'Servicio', 'help_text' => 'Descripción principal del servicio', 'required' => true, 'locked' => false, 'block_config_defaults' => new \stdClass()],
                ['block_key' => 'cta', 'label' => 'Llamado a la acción', 'help_text' => 'Invita a contactar o cotizar', 'required' => false, 'locked' => false, 'block_config_defaults' => new \stdClass()],
            ], [
                ['step_title' => 'Nombre y descripción', 'step_hint' => 'Nombre del servicio y una breve explicación', 'fields' => [
                    ['key' => 'title', 'label' => 'Nombre', 'type' => 'text', 'required' => true],
                    ['key' => 'excerpt', 'label' => 'Descripción', 'type' => 'textarea', 'required' => false],
                ]],
                ['step_title' => 'Imagen destacada', 'step_hint' => 'Portada del servicio (biblioteca o URL)', 'fields' => [['key' => 'featured_image', 'label' => 'Imagen destacada', 'type' => 'image', 'required' => false]]],
            ]),
            self::collectionPreset('other', [
                ['block_key' => 'rich_text', 'label' => 'Contenido', 'help_text' => 'Punto de partida genérico', 'required' => true, 'locked' => false, 'block_config_defaults' => new \stdClass()],
            ], [
                ['step_title' => 'Título y resumen', 'step_hint' => 'Nombre visible de la entrada y un breve contexto', 'fields' => [
                    ['key' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
                    ['key' => 'excerpt', 'label' => 'Resumen', 'type' => 'textarea', 'required' => false],
                ]],
                ['step_title' => 'Imagen destacada', 'step_hint' => 'Portada de la entrada (biblioteca o URL)', 'fields' => [['key' => 'featured_image', 'label' => 'Imagen destacada', 'type' => 'image', 'required' => false]]],
            ]),
        ];
    }

    /**
     * Keeps a preset's blocks that reference an active block type only, dropping the whole
     * preset only when none of its blocks survive. Each returned preset also carries
     * `missing_blocks` — the `block_key` values from its original definition that were dropped
     * because that type doesn't exist in this deployment's active block catalog — so the caller
     * can explain a partial preset instead of the previous all-or-nothing behavior, where a
     * single missing block type silently hid the entire preset with no indication why.
     *
     * @param array<int, array<string, mixed>> $presets
     * @param list<string> $activeBlockKeys
     * @return array<int, array<string, mixed>>
     */
    public static function filterAvailablePresets(array $presets, array $activeBlockKeys): array
    {
        if ($activeBlockKeys === []) {
            return [];
        }

        $available = [];

        foreach ($presets as $preset) {
            $blocks = $preset['block_template']['blocks'] ?? null;
            if (! is_array($blocks) || $blocks === []) {
                $preset['missing_blocks'] = [];
                $available[] = $preset;
                continue;
            }

            $keptBlocks = [];
            $missingBlocks = [];

            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $blockKey = (string) ($block['block_key'] ?? '');
                if ($blockKey === '' || in_array($blockKey, $activeBlockKeys, true)) {
                    $keptBlocks[] = $block;
                } else {
                    $missingBlocks[] = $blockKey;
                }
            }

            if ($keptBlocks === []) {
                continue;
            }

            $preset['block_template']['blocks'] = $keptBlocks;
            $preset['missing_blocks'] = $missingBlocks;
            $available[] = $preset;
        }

        return $available;
    }

    /**
     * @return array<int, array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: null}>
     */
    public static function pagePresets(): array
    {
        return [
            self::pagePreset('home', [
                ['block_key' => 'hero_slider', 'label' => 'Hero principal', 'help_text' => 'Bloque de bienvenida', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) []],
                ['block_key' => 'collection_grid', 'label' => 'Últimas entradas', 'help_text' => 'Grilla de una colección', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['collection_key' => '', 'items_limit' => 3, 'order_by' => 'published_at', 'order_direction' => 'desc', 'layout_variant' => 'cards', 'css_class' => '']],
                ['block_key' => 'cta', 'label' => 'Llamado a la acción', 'help_text' => 'Invitación final', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['variant' => 'blue', 'css_class' => '']],
            ]),
            self::pagePreset('generic', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título y breadcrumb', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Contenido', 'help_text' => 'Bloque editorial principal', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('contact', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'form_embed', 'label' => 'Formulario', 'help_text' => 'Formulario CMS embebido', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['form_key' => 'contact', 'show_info_boxes' => true, 'css_class' => '']],
                ['block_key' => 'contact_info', 'label' => 'Datos de contacto', 'help_text' => 'Información estructurada de contacto', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['layout' => 'stacked', 'css_class' => '']],
                ['block_key' => 'map_embed', 'label' => 'Mapa', 'help_text' => 'Mapa o iframe embebido', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['embed_url' => '', 'aspect_ratio' => '16/9', 'height' => 360, 'css_class' => '']],
                ['block_key' => 'social_links', 'label' => 'Redes', 'help_text' => 'Enlaces sociales', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('privacy', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título y breadcrumb', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Política', 'help_text' => 'Texto legal', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('terms', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título y breadcrumb', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Términos', 'help_text' => 'Texto legal', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('about', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Introducción', 'help_text' => 'Bloque editorial base', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
                ['block_key' => 'image', 'label' => 'Imagen', 'help_text' => 'Apoyo visual', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['aspect_ratio' => '16/9', 'css_class' => '']],
                ['block_key' => 'cta', 'label' => 'Llamado a la acción', 'help_text' => 'Invitación final', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['variant' => 'blue', 'css_class' => '']],
            ]),
            self::pagePreset('history', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Historia', 'help_text' => 'Bloque editorial base', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
                ['block_key' => 'image', 'label' => 'Imagen', 'help_text' => 'Apoyo visual', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['aspect_ratio' => '16/9', 'css_class' => '']],
                ['block_key' => 'metrics_grid', 'label' => 'Métricas', 'help_text' => 'Grilla de cifras o hitos', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['variant' => 'dark', 'css_class' => '']],
                ['block_key' => 'accordion', 'label' => 'Acordeón', 'help_text' => 'Lista de elementos desplegables', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
                ['block_key' => 'cta', 'label' => 'Llamado a la acción', 'help_text' => 'Invitación final', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['variant' => 'blue', 'css_class' => '']],
            ]),
            self::pagePreset('events', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'collection_grid', 'label' => 'Cartelera', 'help_text' => 'Grilla de una colección', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['collection_key' => '', 'items_limit' => 6, 'order_by' => 'published_at', 'order_direction' => 'asc', 'layout_variant' => 'cards', 'css_class' => '']],
                ['block_key' => 'image', 'label' => 'Imagen', 'help_text' => 'Apoyo visual', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['aspect_ratio' => '16/9', 'css_class' => '']],
            ]),
            self::pagePreset('collection_index', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página índice', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Introducción', 'help_text' => 'Contenido editorial antes del listado', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
                ['block_key' => 'collection_listing', 'label' => 'Listado de colección', 'help_text' => 'Listado completo administrable', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['collection_id' => 0, 'per_page' => 12, 'order_by' => 'published_at', 'order_direction' => 'desc', 'layout_variant' => 'cards', 'show_search' => true, 'show_categories' => true, 'show_tags' => false, 'show_excerpt' => true, 'show_date' => true, 'show_button' => true, 'show_item_categories' => true, 'show_extra_richtext' => false, 'show_extra_link' => false, 'show_extra_image' => false, 'css_class' => '']],
                ['block_key' => 'cta', 'label' => 'Cierre', 'help_text' => 'Contenido editorial después del listado', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['variant' => 'blue', 'css_class' => '']],
            ]),
            self::pagePreset('maintenance', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Mensaje', 'help_text' => 'Aviso temporal', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('404', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Mensaje', 'help_text' => 'Mensaje de error', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
            self::pagePreset('500', [
                ['block_key' => 'page_header', 'label' => 'Encabezado', 'help_text' => 'Título de la página', 'required' => true, 'locked' => false, 'block_config_defaults' => (object) ['bg_color' => 'bg-gray-100', 'css_class' => '']],
                ['block_key' => 'rich_text', 'label' => 'Mensaje', 'help_text' => 'Mensaje de error', 'required' => false, 'locked' => false, 'block_config_defaults' => (object) ['css_class' => '']],
            ]),
        ];
    }

    /**
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: array<string, mixed>|null}
     */
    public static function resolve(string $entityKind, string $typeKey): array
    {
        $kind = strtolower(trim($entityKind));
        $type = trim($typeKey);

        if ($kind === 'collection') {
            return self::resolveCollection($type);
        }

        return self::resolvePage($type);
    }

    /**
     * @return list<string>
     */
    public static function optionKeys(string $entityKind): array
    {
        return strtolower(trim($entityKind)) === 'collection'
            ? self::collectionTypes()
            : self::pageTypes();
    }

    /**
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: array<string, mixed>|null}
     */
    public static function resolveCollection(string $typeKey): array
    {
        $typeKey = strtolower(trim($typeKey));
        foreach (self::collectionPresets() as $preset) {
            if ($preset['type_key'] === $typeKey) {
                return $preset;
            }
        }

        return self::resolveCollection('other');
    }

    /**
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: null}
     */
    public static function resolvePage(string $typeKey): array
    {
        $typeKey = strtolower(trim($typeKey));
        foreach (self::pagePresets() as $preset) {
            if ($preset['type_key'] === $typeKey) {
                return $preset;
            }
        }

        return self::resolvePage('generic');
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @param array<int, array<string, mixed>>|null $wizardConfig
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: array<string, mixed>|null}
     */
    private static function collectionPreset(string $typeKey, array $blocks, ?array $wizardConfig): array
    {
        return self::preset('collection', $typeKey, $blocks, $wizardConfig);
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: null}
     */
    private static function pagePreset(string $typeKey, array $blocks): array
    {
        $normalizedBlocks = [];
        foreach (array_values($blocks) as $index => $block) {
            $block['sort_order'] = $index + 1;
            $normalizedBlocks[] = $block;
        }

        return [
            'type_key' => $typeKey,
            'label' => ucfirst(str_replace(['-', '_'], ' ', $typeKey)),
            'version' => '1.0',
            'block_template' => [
                'version' => '1.0',
                'blocks' => $normalizedBlocks,
            ],
            'wizard_config' => null,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $blocks
     * @param array<int, array<string, mixed>>|null $wizardConfig
     * @return array{type_key: string, label: string, version: string, block_template: array<string, mixed>, wizard_config: array<string, mixed>|null}
     */
    private static function preset(string $kind, string $typeKey, array $blocks, ?array $wizardConfig): array
    {
        $normalizedBlocks = [];
        foreach (array_values($blocks) as $index => $block) {
            $block['sort_order'] = $index + 1;
            $normalizedBlocks[] = $block;
        }

        return [
            'type_key' => $typeKey,
            'label' => ucfirst(str_replace(['-', '_'], ' ', $typeKey)),
            'version' => '1.0',
            'block_template' => [
                'version' => '1.0',
                'blocks' => $normalizedBlocks,
            ],
            'wizard_config' => $kind === 'collection'
                ? ['type' => $typeKey, 'steps' => $wizardConfig ?? []]
                : null,
        ];
    }
}
