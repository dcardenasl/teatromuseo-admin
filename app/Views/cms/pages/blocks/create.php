<?php
$page             = $page             ?? [];
$blockTypes       = $blockTypes       ?? [];
$languages        = $languages        ?? [];
$parentInstanceId = $parentInstanceId ?? null;
$parentBlockType  = $parentBlockType  ?? null;
$ownerType        = $ownerType        ?? 'page';
$ownerLabel       = $ownerLabel       ?? 'Página';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerStoreRoute  = $ownerStoreRoute  ?? 'admin.cms.pages.blocks.store';
$ownerChildLabel  = $ownerChildLabel  ?? 'Diapositiva';
$translateUrl     = $translateUrl     ?? '';
$defaultLangCode   = $defaultLangCode   ?? 'ES';

// When creating a child block, filter dynamically using parent block's allowed_children schema definition
if ($parentInstanceId !== null) {
    $allowedChildren = [];
    if ($parentBlockType !== null) {
        $parentSchema = is_array($parentBlockType['schema_definition'] ?? [])
            ? ($parentBlockType['schema_definition'] ?? [])
            : json_decode((string)($parentBlockType['schema_definition'] ?? '{}'), true);
        $allowedChildren = $parentSchema['allowed_children'] ?? [];
    }

    $blockTypes = array_values(array_filter(
        $blockTypes,
        static fn (array $bt) => in_array($bt['block_key'], $allowedChildren, true)
    ));
} else {
    // When creating a top-level block, exclude child-only block types dynamically.
    // A block type is child-only if it is allowed as a child of some container,
    // but is NOT allowed in the generic layout 'container' block.
    $containerAllowedChildren = [];
    foreach ($blockTypes as $bt) {
        if (($bt['block_key'] ?? '') === 'container') {
            $schema = is_array($bt['schema_definition'] ?? [])
                ? ($bt['schema_definition'] ?? [])
                : json_decode((string) ($bt['schema_definition'] ?? '{}'), true);
            $containerAllowedChildren = $schema['allowed_children'] ?? [];
            break;
        }
    }

    if (! empty($containerAllowedChildren)) {
        $allChildrenKeys = [];
        foreach ($blockTypes as $bt) {
            $schema = is_array($bt['schema_definition'] ?? [])
                ? ($bt['schema_definition'] ?? [])
                : json_decode((string) ($bt['schema_definition'] ?? '{}'), true);
            $allowed = $schema['allowed_children'] ?? [];
            foreach ($allowed as $childKey) {
                $allChildrenKeys[] = $childKey;
            }
        }
        $allChildrenKeys = array_unique($allChildrenKeys);
        $childOnlyKeys   = array_diff($allChildrenKeys, $containerAllowedChildren);

        $blockTypes = array_values(array_filter(
            $blockTypes,
            static fn (array $bt) => ! in_array($bt['block_key'], $childOnlyKeys, true)
        ));
    }
}

$blockTypesJs  = json_encode(array_values($blockTypes), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$languagesJs   = json_encode(array_values($languages), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$entryOptionsUrlJs = json_encode((string) ($entryOptionsUrl ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$previewUrl    = route_to('admin.cms.blocks.preview');
$parentIdJs    = json_encode($parentInstanceId);
$isImageAccept = static function (string $accept): bool {
    $normalized = strtolower(trim($accept));

    return $normalized === 'image'
        || $normalized === 'image/*'
        || str_starts_with($normalized, 'image/');
};
?>
<meta name="block-preview-url" content="<?= esc($previewUrl) ?>">

<div class="mb-4">
    <?php if ($parentInstanceId !== null): ?>
        <a href="javascript:history.back()" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerChildLabel) ?>
        </a>
    <?php else: ?>
        <a href="<?= route_to($ownerBlocksRoute, (string)$page['id']) ?>" class="text-sm text-brand-600 hover:text-brand-700">
            &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerLabel) ?>
        </a>
    <?php endif; ?>
</div>

<div x-data="blockInstanceBuilder(<?= esc($blockTypesJs, 'attr') ?>, <?= esc($languagesJs, 'attr') ?>, <?= esc($entryOptionsUrlJs, 'attr') ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')" class="space-y-6">
    <?php ob_start(); ?>
    <div class="relative mb-4">
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="text"
               x-model="blockTypeSearch"
               placeholder="<?= esc(lang('Pages.block_type_search_placeholder')) ?>"
               class="block w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
    </div>
    <p x-show="blockTypeSearch.trim() !== '' && filteredBlockTypes().length === 0" x-cloak class="text-sm text-gray-400 py-6 text-center">
        <?= esc(lang('Pages.block_type_search_empty')) ?>
    </p>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <template x-for="bt in filteredBlockTypes()" :key="bt.id">
            <button type="button"
                @click="selectBlockType(bt)"
                :class="selectedBlockType?.id === bt.id
                    ? 'border-brand-600 bg-brand-50 ring-2 ring-brand-400'
                    : 'border-gray-200 bg-white hover:border-brand-400 hover:bg-brand-50/30'"
                class="relative flex flex-col items-center gap-2 p-4 border-2 rounded-xl text-center cursor-pointer transition-all">

                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500"
                     :class="selectedBlockType?.id === bt.id ? 'bg-brand-100 text-brand-600' : ''">
                    <i :data-lucide="bt.icon || 'layout-template'" class="w-5 h-5"></i>
                </div>
                <span class="text-xs font-semibold text-gray-800 leading-tight" x-text="bt.name"></span>
                <code class="text-[10px] text-gray-400 font-mono" x-text="bt.block_key"></code>

                <span x-show="selectedBlockType?.id === bt.id"
                      class="absolute top-2 right-2 w-4 h-4 bg-brand-600 rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.485 1.431a1.473 1.473 0 0 1 2.104 0 1.473 1.473 0 0 1 0 2.104L6.555 12.64 1.127 7.212a1.473 1.473 0 0 1 0-2.104 1.474 1.474 0 0 1 2.104 0l3.324 3.324 6.93-6.94z"/>
                    </svg>
                </span>
            </button>
        </template>
    </div>

    <div x-show="selectedBlockType" x-cloak class="mt-4 p-3 bg-brand-50 border border-brand-200 rounded-lg text-sm text-brand-800 flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <span x-text="selectedBlockType?.description || ''"></span>
    </div>
    <?php $step1Content = ob_get_clean(); ?>
    <?= view('components/display/form_section', [
        'title' => 'Pages.block_step1_title',
        'description' => 'Pages.block_step1_desc',
        'content' => $step1Content,
        'bodyClass' => 'space-y-4',
    ]) ?>

    <div x-show="selectedBlockType" x-cloak>
        <?php ob_start(); ?>
        <div class="flex items-center justify-end mb-5">
            <button type="button"
                @click="openPreview()"
                class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                <?= esc(lang('Pages.block_preview_button')) ?>
            </button>
        </div>

        <form method="post" action="<?= route_to($ownerStoreRoute, (string)$page['id']) ?>" class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="block_id" :value="selectedBlockType?.id">
            <?php if ($parentInstanceId !== null): ?>
                <input type="hidden" name="parent_instance_id" value="<?= esc((string) $parentInstanceId) ?>">
            <?php endif; ?>
            <input type="hidden" name="sort_order" value="0">
            <div class="flex items-center pb-4 border-b border-gray-100">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <label for="is_active" class="ml-2 block text-sm font-medium text-gray-700"><?= esc(lang('Pages.block_active_label')) ?></label>
            </div>

            <div x-show="configFields && Object.keys(configFields).length > 0" x-cloak>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Configuración del Diseño</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <template x-for="(field, key) in configFields" :key="key">
                        <div>
                            <template x-if="field.type !== 'media_reference'">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    <span x-text="field.label || key"></span>
                                </label>
                            </template>

                            <template x-if="field.type === 'select'">
                                <div>
                                    <template x-if="key === 'collection_id'">
                                        <select :name="`block_config[${key}]`"
                                                x-model="collectionId"
                                                @change="onCollectionChange($event.target.value)"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                            <option value="">— Seleccionar —</option>
                                            <template x-for="opt in (field.options || [])" :key="typeof opt === 'object' ? opt.value : opt">
                                                <option :value="typeof opt === 'object' ? opt.value : opt" x-text="typeof opt === 'object' ? opt.label : opt"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="key === 'entry_id'">
                                        <div class="space-y-1">
                                            <select :name="`block_config[${key}]`"
                                                    x-model="entryId"
                                                    :disabled="!collectionId || entryOptionsLoading"
                                                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:bg-gray-100">
                                                <option value="" x-text="entryOptionsLoading ? 'Cargando entradas...' : '— Seleccionar —'"></option>
                                                <template x-for="opt in entryOptions" :key="opt.value">
                                                    <option :value="opt.value" x-text="opt.label"></option>
                                                </template>
                                            </select>
                                            <p x-show="!collectionId" class="text-[11px] text-gray-400">Selecciona primero una colección.</p>
                                            <p x-show="entryOptionsError" class="text-[11px] text-red-500" x-text="entryOptionsError"></p>
                                        </div>
                                    </template>
                                    <template x-if="key !== 'collection_id' && key !== 'entry_id'">
                                        <select :name="`block_config[${key}]`"
                                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                            <template x-for="opt in (field.options || [])" :key="typeof opt === 'object' ? opt.value : opt">
                                                <option :value="typeof opt === 'object' ? opt.value : opt" :selected="(typeof opt === 'object' ? opt.value : opt) == (field.default || '')" x-text="typeof opt === 'object' ? opt.label : opt"></option>
                                            </template>
                                        </select>
                                    </template>
                                </div>
                            </template>
                            <template x-if="field.type === 'color'">
                                <div x-data="{ 
                                    value: field.default || '', 
                                    open: false,
                                    presets: [
                                        { hex: '', name: 'Transparent' },
                                        { hex: '#ffffff', name: 'Blanco' },
                                        { hex: '#000000', name: 'Negro' },
                                        { hex: '#3b82f6', name: 'Azul' },
                                        { hex: '#10b981', name: 'Verde' },
                                        { hex: '#ef4444', name: 'Rojo' },
                                        { hex: '#f59e0b', name: 'Naranja' },
                                        { hex: '#8b5cf6', name: 'Violeta' },
                                        { hex: '#6b7280', name: 'Gris' },
                                        { hex: '#f3f4f6', name: 'Gris Claro' },
                                        { hex: '#1e3a8a', name: 'Azul Oscuro' },
                                        { hex: '#065f46', name: 'Verde Oscuro' },
                                        { hex: '#991b1b', name: 'Rojo Oscuro' }
                                    ]
                                }" @click.outside="open = false" class="relative">
                                    <div class="mt-1 flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="h-10 w-10 shrink-0 rounded-lg border border-gray-300 shadow-sm transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                            :style="value ? `background-color: ${value}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
                                        ></button>
                                        <div class="flex-1 relative">
                                            <input
                                                type="text"
                                                :name="`block_config[${key}]`"
                                                x-model="value"
                                                placeholder="#ffffff o rgb(...)"
                                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 uppercase shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 pl-3 pr-10"
                                            >
                                            <button
                                                type="button"
                                                @click="open = !open"
                                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div
                                        x-show="open"
                                        class="absolute left-0 z-50 mt-2 p-3 bg-white border border-gray-200 rounded-xl shadow-xl w-64 max-w-sm"
                                        x-cloak
                                    >
                                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Paleta Predefinida</span>
                                        <div class="grid grid-cols-5 gap-2 mb-3">
                                            <template x-for="p in presets" :key="p.hex">
                                                <button
                                                    type="button"
                                                    @click="value = p.hex; open = false"
                                                    :title="p.name"
                                                    class="h-8 w-8 rounded-lg border border-gray-200 shadow-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                                    :class="value === p.hex ? 'ring-2 ring-brand-500 scale-105 border-brand-500' : ''"
                                                    :style="p.hex ? `background-color: ${p.hex}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
                                                ></button>
                                            </template>
                                        </div>
                                        <div class="border-t border-gray-100 pt-3 flex items-center justify-between gap-2">
                                            <span class="text-xs text-gray-500">Personalizado:</span>
                                            <input
                                                type="color"
                                                x-model="value"
                                                class="h-8 w-8 cursor-pointer rounded border border-gray-200 p-0 bg-transparent"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="field.type === 'media_reference'">
                                <div x-data="mediaReferenceField(field.default || {}, field.accept || 'image', fieldKey)" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <label class="block text-xs font-semibold text-gray-700">
                                                <span x-text="field.label || key"></span>
                                                <span x-show="field.required" class="text-red-500 ml-0.5">*</span>
                                            </label>
                                            <p class="mt-1 text-[11px] text-gray-500">Elige una imagen desde la biblioteca o pega una URL externa pública.</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium" :class="sourceKindBadgeClass()">
                                            <span class="mr-1.5 h-2 w-2 rounded-full" :class="sourceKind === 'external_url' ? 'bg-brand-500' : 'bg-emerald-500'"></span>
                                            <span x-text="sourceKindLabel()"></span>
                                        </span>
                                    </div>

                                    <div class="grid gap-2 sm:grid-cols-2" role="group" :aria-label="field.label || key">
                                        <button type="button"
                                                @click="setSourceKind('hub_file')"
                                                :aria-pressed="isFileSource()"
                                                class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
                                                :class="sourceKindButtonClass('hub_file')">
                                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/80 ring-1 ring-inset ring-current/10">
                                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 6.75 11.25a2.25 2.25 0 0 1 3.182 0l4.5 4.5m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold">Biblioteca</span>
                                                    <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('hub_file')"></span>
                                                </div>
                                                <p class="mt-0.5 text-xs text-gray-500">Selecciona un archivo existente o sube uno nuevo.</p>
                                            </div>
                                        </button>
                                        <button type="button"
                                                @click="setSourceKind('external_url')"
                                                :aria-pressed="isExternalSource()"
                                                class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
                                                :class="sourceKindButtonClass('external_url')">
                                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/80 ring-1 ring-inset ring-current/10">
                                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5h3.75a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5h-3.75m-7.5-10.5H4.5A1.5 1.5 0 0 0 3 9v6a1.5 1.5 0 0 0 1.5 1.5h3.75m0-10.5L12 3.75m0 0 3.75 3.75M12 3.75V16.5" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold">URL externa</span>
                                                    <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('external_url')"></span>
                                                </div>
                                                <p class="mt-0.5 text-xs text-gray-500">Pega un enlace público directo a la imagen.</p>
                                            </div>
                                        </button>
                                    </div>

                                    <input type="hidden" :name="`block_config[${key}][source_kind]`" x-model="sourceKind">
                                    <input type="hidden" :name="`block_config[${key}][file_id]`" x-model="fileId">
                                    <input :type="isExternalSource() ? 'url' : 'hidden'"
                                           :name="`block_config[${key}][url]`"
                                           x-model="url"
                                           @input="syncExternalUrl()"
                                           placeholder="https://..."
                                           inputmode="url"
                                           spellcheck="false"
                                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">

                                    <div x-show="previewUrl" x-cloak class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                        <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-3 py-2">
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Vista previa</span>
                                            <span class="text-[11px] text-gray-400" x-text="sourceKindLabel()"></span>
                                        </div>
                                        <template x-if="accept === 'video'">
                                            <video :src="previewUrl" class="h-36 w-full rounded-none border-0 object-cover" controls muted></video>
                                        </template>
                                        <template x-if="accept === 'image' || accept === 'any'">
                                            <img :src="previewUrl" class="h-36 w-full rounded-none border-0 object-cover">
                                        </template>
                                        <template x-if="accept === 'document' || accept === 'audio'">
                                            <a :href="previewUrl" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-3 text-xs text-gray-600 hover:bg-gray-100">
                                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                                <span class="truncate" x-text="previewUrl"></span>
                                            </a>
                                        </template>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button"
                                                @click="openPicker()"
                                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                            </svg>
                                            <span x-text="pickerButtonLabel()"></span>
                                        </button>
                                        <button type="button"
                                                @click="clearReference()"
                                                x-show="fileId || url"
                                                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                            </svg>
                                            <span>Quitar</span>
                                        </button>
                                    </div>
                                    <p class="rounded-xl border border-dashed border-gray-200 bg-gray-50/70 px-3 py-2 text-[11px] text-gray-500" x-text="sourceKindHint()"></p>
                                </div>
                            </template>

                            <template x-if="field.type !== 'select' && field.type !== 'boolean' && field.type !== 'color' && field.type !== 'file' && field.type !== 'media_reference' && field.type !== 'repeater'">
                                <input type="text" :name="`block_config[${key}]`"
                                       :value="field.default || ''"
                                       :placeholder="field.default || ''"
                                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            </template>

                            <p x-show="field.description" class="text-[11px] text-gray-400 mt-0.5" x-text="field.description"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="contentFields && Object.keys(contentFields).length > 0" x-cloak>
                <h4 class="text-sm font-semibold text-gray-800 mb-3">Contenido por Idioma</h4>

                <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                    <div class="flex" role="tablist">
                        <template x-for="(lang, index) in languages" :key="lang.id">
                            <button type="button"
                                    role="tab"
                                    @click="activeLangId = lang.id"
                                    :aria-selected="activeLangId == lang.id"
                                    :class="activeLangId == lang.id ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                <span x-text="lang.code.toUpperCase()"></span>
                                <span x-show="lang.is_default == 1" class="ml-1 text-brand-400">★</span>
                            </button>
                        </template>
                    </div>

                    <!-- Translate All button -->
                    <template x-if="getTranslateTargets().length > 0">
                        <button type="button"
                                @click="autoTranslateAll()"
                                :disabled="translatingAll"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 shadow-sm hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621c0-.012 0-.024 0-.036V3.75a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 20.25 21H3.75A2.25 2.25 0 0 1 1.5 18.75Zm12.621-4.72l-6.89 7.72m0 0l-6.89-7.72m6.89 7.72l6.89-7.72m-6.89 7.72l-6.89 7.72"/>
                            </svg>
                            <span x-text="translatingAll ? 'Traduciendo...' : 'Traducir automáticamente'"></span>
                        </button>
                    </template>
                </div>

                <p x-show="translateError !== ''"
                   x-text="translateError"
                   x-cloak
                   class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                <template x-for="(lang, langIndex) in languages" :key="lang.id">
                    <div x-show="activeLangId == lang.id" class="space-y-4">
                        <input type="hidden" :name="`translations[${langIndex}][language_id]`" :value="lang.id">
                        <input type="hidden" :name="`translations[${langIndex}][is_published]`" value="1">

                        <template x-for="(field, fieldKey) in contentFields" :key="fieldKey">
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-gray-700">
                                    <span x-text="field.label || fieldKey"></span>
                                    <span x-show="field.required && lang.is_default == 1" class="text-red-500 ml-0.5">*</span>
                                </label>

                                <template x-if="field.type === 'richtext'">
                                    <div x-data="richTextEditor('', `translations[${langIndex}][block_data][${fieldKey}]`)"
                                         x-init="init()"
                                         class="border border-gray-300 rounded-lg overflow-hidden bg-white focus-within:ring-2 focus-within:ring-brand-500 focus-within:border-brand-500 transition-shadow">
                                        <?= view('partials/richtext_toolbar') ?>
                                        <!-- Editor area -->
                                        <div x-ref="editorEl" class="richtext-content px-3 py-2.5 min-h-[120px] text-sm text-gray-800 cursor-text"></div>
                                        <!-- Dynamic hidden input (name bound from outer Alpine scope) -->
                                        <input type="hidden" :name="inputName" x-ref="hiddenInput"
                                               :required="field.required && lang.is_default == 1">
                                    </div>
                                </template>
                                <template x-if="field.type === 'text' || field.type === 'textarea'">
                                    <textarea :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                              rows="4"
                                              :required="field.required && lang.is_default == 1"
                                              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                                </template>
                                <template x-if="field.type === 'url'">
                                    <input type="text" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           placeholder="https:// o /ruta"
                                           inputmode="url"
                                           spellcheck="false"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                </template>
                                <template x-if="field.type === 'integer' || field.type === 'int'">
                                    <input type="number" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                </template>
                                <template x-if="field.type === 'select'">
                                    <select :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                            :required="field.required && lang.is_default == 1"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <template x-for="opt in (field.options || [])" :key="typeof opt === 'object' ? opt.value : opt">
                                            <option :value="typeof opt === 'object' ? opt.value : opt" :selected="(typeof opt === 'object' ? opt.value : opt) == (field.default || '')" x-text="typeof opt === 'object' ? opt.label : opt"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="field.type === 'media_reference'">
                                    <div x-data="mediaReferenceField(field.default || {}, field.accept || 'image', fieldKey)" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <label class="block text-xs font-semibold text-gray-700">
                                                    <span x-text="field.label || fieldKey"></span>
                                                    <span x-show="field.required && lang.is_default == 1" class="text-red-500 ml-0.5">*</span>
                                                </label>
                                                <p class="mt-1 text-[11px] text-gray-500">Elige una imagen desde la biblioteca o pega una URL externa pública.</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium" :class="sourceKindBadgeClass()">
                                                <span class="mr-1.5 h-2 w-2 rounded-full" :class="sourceKind === 'external_url' ? 'bg-brand-500' : 'bg-emerald-500'"></span>
                                                <span x-text="sourceKindLabel()"></span>
                                            </span>
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2" role="group" :aria-label="field.label || fieldKey">
                                            <button type="button"
                                                    @click="setSourceKind('hub_file')"
                                                    :aria-pressed="isFileSource()"
                                                    class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
                                                    :class="sourceKindButtonClass('hub_file')">
                                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/80 ring-1 ring-inset ring-current/10">
                                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75 6.75 11.25a2.25 2.25 0 0 1 3.182 0l4.5 4.5m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold">Biblioteca</span>
                                                        <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('hub_file')"></span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-gray-500">Selecciona un archivo existente o sube uno nuevo.</p>
                                                </div>
                                            </button>
                                            <button type="button"
                                                    @click="setSourceKind('external_url')"
                                                    :aria-pressed="isExternalSource()"
                                                    class="flex w-full items-start gap-3 rounded-xl border p-3 text-left transition"
                                                    :class="sourceKindButtonClass('external_url')">
                                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/80 ring-1 ring-inset ring-current/10">
                                                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5h3.75a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5h-3.75m-7.5-10.5H4.5A1.5 1.5 0 0 0 3 9v6a1.5 1.5 0 0 0 1.5 1.5h3.75m0-10.5L12 3.75m0 0 3.75 3.75M12 3.75V16.5" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold">URL externa</span>
                                                        <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('external_url')"></span>
                                                    </div>
                                                    <p class="mt-0.5 text-xs text-gray-500">Pega un enlace público directo a la imagen.</p>
                                                </div>
                                            </button>
                                        </div>

                                        <input type="hidden" :name="`translations[${langIndex}][block_data][${fieldKey}][source_kind]`" x-model="sourceKind">
                                        <input type="hidden" :name="`translations[${langIndex}][block_data][${fieldKey}][file_id]`" x-model="fileId">
                                        <input :type="isExternalSource() ? 'url' : 'hidden'"
                                               :name="`translations[${langIndex}][block_data][${fieldKey}][url]`"
                                               x-model="url"
                                               @input="syncExternalUrl()"
                                               placeholder="https://..."
                                               inputmode="url"
                                               spellcheck="false"
                                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                        <div x-show="previewUrl" x-cloak class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                            <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-3 py-2">
                                                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Vista previa</span>
                                                <span class="text-[11px] text-gray-400" x-text="sourceKindLabel()"></span>
                                            </div>
                                            <template x-if="accept === 'video'">
                                                <video :src="previewUrl" class="h-36 w-full rounded-none border-0 object-cover" controls muted></video>
                                            </template>
                                            <template x-if="accept !== 'video'">
                                                <img :src="previewUrl" class="h-36 w-full rounded-none border-0 object-cover">
                                            </template>
                                        </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="button"
                                                @click="openPicker()"
                                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                </svg>
                                                <span x-text="pickerButtonLabel()"></span>
                                            </button>
                                            <button type="button"
                                                    @click="clearReference()"
                                                    x-show="fileId || url"
                                                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                                </svg>
                                                <span>Quitar</span>
                                            </button>
                                            <?php if (count($languages) > 1): ?>
                                                <button type="button"
                                                        @click="copyToAllLanguages()"
                                                        x-show="fileId || url"
                                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 shadow-sm transition-colors hover:bg-brand-100">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                                                    </svg>
                                                    <span>Copiar a otros idiomas</span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <p class="rounded-xl border border-dashed border-gray-200 bg-gray-50/70 px-3 py-2 text-[11px] text-gray-500" x-text="sourceKindHint()"></p>
                                    </div>
                                </template>
                                <template x-if="field.type === 'repeater'">
                                    <div class="space-y-3">
                                        <template x-for="(item, itemIdx) in repeaterList(lang.id, fieldKey)" :key="itemIdx">
                                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs font-semibold text-gray-600" x-text="`Item ${itemIdx + 1}`"></span>
                                                    <button type="button"
                                                            @click="removeItem(lang.id, fieldKey, itemIdx)"
                                                            class="text-xs text-red-500 hover:text-red-700">
                                                        Eliminar
                                                    </button>
                                        </div>
                                        <template x-for="(subField, subKey) in (field.item_fields || {})" :key="subKey">
                                            <div class="space-y-1">
                                                <template x-if="subField.type !== 'media_reference'">
                                                    <label class="block text-xs font-medium text-gray-600" x-text="subField.label || subKey"></label>
                                                </template>
                                                <template x-if="subField.type === 'media_reference'">
                                                    <div x-data="{ outerFieldKey: fieldKey }">
                                                    <div x-data="mediaReferenceField(item[subKey] || {}, subField.accept || 'image', `${outerFieldKey}][${itemIdx}][${subKey}`)" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                        <input type="hidden"
                                                               :name="`translations[${langIndex}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][source_kind]`"
                                                               x-model="sourceKind">
                                                        <input type="hidden"
                                                               :name="`translations[${langIndex}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][file_id]`"
                                                               x-model="fileId">
                                                        <input :type="isExternalSource() ? 'url' : 'hidden'"
                                                               :name="`translations[${langIndex}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][url]`"
                                                               x-model="url"
                                                               @input="syncExternalUrl()"
                                                               placeholder="https://..."
                                                               inputmode="url"
                                                               spellcheck="false"
                                                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                        <div x-show="previewUrl" x-cloak>
                                                            <template x-if="accept === 'video'">
                                                                <video :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover" controls muted></video>
                                                            </template>
                                                            <template x-if="accept === 'image' || accept === 'any'">
                                                                <img :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                            </template>
                                                            <template x-if="accept === 'document' || accept === 'audio'">
                                                                <a :href="previewUrl" target="_blank" rel="noopener" class="flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-100">
                                                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                    </svg>
                                                                    <span class="truncate" x-text="previewUrl"></span>
                                                                </a>
                                                            </template>
                                                        </div>
                                                        <div class="flex flex-wrap gap-2">
                                                            <button type="button"
                                                                    @click="openPicker()"
                                                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                </svg>
                                                                <span x-text="pickerButtonLabel()"></span>
                                                            </button>
                                                            <button type="button"
                                                                    @click="clearReference()"
                                                                    x-show="fileId || url"
                                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                                                </svg>
                                                                <span>Quitar</span>
                                                            </button>
                                                            <button type="button"
                                                                    @click="copyToAllLanguages()"
                                                                    x-show="fileId || url"
                                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-brand-50 px-3 py-2 text-sm font-medium text-brand-700 shadow-sm transition-colors hover:bg-brand-100">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                                                                </svg>
                                                                <span>Copiar a otros idiomas</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </template>
                                                <template x-if="subField.type === 'file'">
                                                    <div class="space-y-1.5">
                                                        <template x-if="String(subField.accept || 'image').toLowerCase() === 'image' || String(subField.accept || 'image').toLowerCase() === 'image/*' || String(subField.accept || 'image').toLowerCase().startsWith('image/')">
                                                            <div x-data="mediaReferenceField(item[subKey] || {}, subField.accept || 'image')" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}][source_kind]`"
                                                                       x-model="sourceKind">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}][file_id]`"
                                                                       x-model="fileId">
                                                                <input :type="isExternalSource() ? 'url' : 'hidden'"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}][url]`"
                                                                       x-model="url"
                                                                       @input="syncExternalUrl()"
                                                                       placeholder="https://..."
                                                                       inputmode="url"
                                                                       spellcheck="false"
                                                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                                <div x-show="previewUrl" x-cloak>
                                                                    <template x-if="accept === 'video'">
                                                                        <video :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover" controls muted></video>
                                                                    </template>
                                                                    <template x-if="accept !== 'video'">
                                                                        <img :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                                    </template>
                                                                </div>
                                                                <div class="flex flex-wrap gap-2">
                                                                <button type="button"
                                                                        @click="openPicker()"
                                                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                        </svg>
                                                                        <span x-text="pickerButtonLabel()"></span>
                                                                    </button>
                                                                <button type="button"
                                                                        @click="clearReference()"
                                                                        x-show="fileId || url"
                                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                                                        </svg>
                                                                        <span>Quitar</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="!(String(subField.accept || 'image').toLowerCase() === 'image' || String(subField.accept || 'image').toLowerCase() === 'image/*' || String(subField.accept || 'image').toLowerCase().startsWith('image/'))">
                                                            <div class="space-y-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}_file_id]`"
                                                                       :value="item[subKey + '_file_id'] || ''">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}_url]`"
                                                                       :value="item[subKey + '_url'] || ''">
                                                                <div x-show="item[subKey + '_url'] || item[subKey + '_preview_url']">
                                                                    <template x-if="(subField.accept || 'image') === 'video'">
                                                                        <video :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                               class="h-20 w-auto rounded border border-gray-200" controls muted></video>
                                                                    </template>
                                                                    <template x-if="(subField.accept || 'image') !== 'video'">
                                                                        <img :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                             class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                                    </template>
                                                                </div>
                                                                <button type="button"
                                                                        @click="openFilePicker((file) => pickFile(lang.id, fieldKey, itemIdx, subKey, file), subField.accept || 'image')"
                                                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                    </svg>
                                                                    <span x-text="item[subKey + '_file_id'] ? 'Cambiar' : 'Seleccionar'"></span>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="subField.type === 'url'">
                                                    <input type="text"
                                                           :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           placeholder="https:// o /ruta"
                                                                   inputmode="url"
                                                                   spellcheck="false"
                                                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                        </template>
                                                        <template x-if="subField.type === 'text' || subField.type === 'textarea'">
                                                            <textarea :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                                      x-model="item[subKey]"
                                                                      rows="3"
                                                              class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                                                        </template>
                                                <template x-if="!['media_reference','url','text','textarea'].includes(subField.type)">
                                                    <input type="text"
                                                           :name="`translations[${langIndex}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <button type="button"
                                                @click="addItem(lang.id, fieldKey, field.item_fields || {})"
                                                class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                            Agregar item
                                        </button>
                                    </div>
                                </template>
                                <template x-if="field.type === 'color'">
                                    <div x-data="{ 
                                        value: field.default || '', 
                                        open: false,
                                        presets: [
                                            { hex: '', name: 'Transparent' },
                                            { hex: '#ffffff', name: 'Blanco' },
                                            { hex: '#000000', name: 'Negro' },
                                            { hex: '#3b82f6', name: 'Azul' },
                                            { hex: '#10b981', name: 'Verde' },
                                            { hex: '#ef4444', name: 'Rojo' },
                                            { hex: '#f59e0b', name: 'Naranja' },
                                            { hex: '#8b5cf6', name: 'Violeta' },
                                            { hex: '#6b7280', name: 'Gris' },
                                            { hex: '#f3f4f6', name: 'Gris Claro' },
                                            { hex: '#1e3a8a', name: 'Azul Oscuro' },
                                            { hex: '#065f46', name: 'Verde Oscuro' },
                                            { hex: '#991b1b', name: 'Rojo Oscuro' }
                                        ]
                                    }" @click.outside="open = false" class="relative">
                                        <div class="mt-1 flex items-center gap-2">
                                            <button
                                                type="button"
                                                @click="open = !open"
                                                class="h-10 w-10 shrink-0 rounded-lg border border-gray-300 shadow-sm transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                                :style="value ? `background-color: ${value}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
                                            ></button>
                                            <div class="flex-1 relative">
                                                <input
                                                    type="text"
                                                    :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                                    x-model="value"
                                                    placeholder="#ffffff o rgb(...)"
                                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 uppercase shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 pl-3 pr-10"
                                                    :required="field.required && lang.is_default == 1"
                                                >
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div
                                            x-show="open"
                                            class="absolute left-0 z-50 mt-2 p-3 bg-white border border-gray-200 rounded-xl shadow-xl w-64 max-w-sm"
                                            x-cloak
                                        >
                                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Paleta Predefinida</span>
                                            <div class="grid grid-cols-5 gap-2 mb-3">
                                                <template x-for="p in presets" :key="p.hex">
                                                    <button
                                                        type="button"
                                                        @click="value = p.hex; open = false"
                                                        :title="p.name"
                                                        class="h-8 w-8 rounded-lg border border-gray-200 shadow-sm transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                                        :class="value === p.hex ? 'ring-2 ring-brand-500 scale-105 border-brand-500' : ''"
                                                        :style="p.hex ? `background-color: ${p.hex}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
                                                    ></button>
                                                </template>
                                            </div>
                                            <div class="border-t border-gray-100 pt-3 flex items-center justify-between gap-2">
                                                <span class="text-xs text-gray-500">Personalizado:</span>
                                                <input
                                                    type="color"
                                                    x-model="value"
                                                    class="h-8 w-8 cursor-pointer rounded border border-gray-200 p-0 bg-transparent"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!['richtext','text','textarea','url','integer','int','select','file','repeater','color'].includes(field.type)">
                                    <input type="text" :name="`translations[${langIndex}][block_data][${fieldKey}]`"
                                           :required="field.required && lang.is_default == 1"
                                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                </template>

                                <p x-show="field.type === 'richtext'" class="text-[10px] text-gray-400">Ctrl+B negrita · Ctrl+I cursiva · Ctrl+K enlace</p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div x-show="selectedBlockType && Object.keys(contentFields).length === 0" x-cloak
                 class="p-4 bg-gray-50 border border-dashed border-gray-300 rounded-lg text-sm text-gray-500 text-center">
                <?= esc(lang('Pages.block_structural_note')) ?>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('Pages.block_add_button')) ?></button>
                <a href="<?= route_to($ownerBlocksRoute, (string)$page['id']) ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
            </div>
        </form>
        <?php $step2Content = ob_get_clean(); ?>
        <?= view('components/display/form_section', [
            'title' => 'Pages.block_step2_title',
            'description' => 'Pages.block_step2_desc',
            'content' => $step2Content,
            'bodyClass' => 'space-y-6',
        ]) ?>
    </div>


</div>

<script>
const findRichTextEditorComponent = (input) => {
    const container = input instanceof HTMLElement ? input.closest('[x-data*="richTextEditor"]') : null;
    const component = container?._x_dataStack?.[0];
    return component && typeof component.applyContent === 'function' ? component : null;
};

const applyTranslatedText = (targetInput, translatedValue) => {
    if (!(targetInput instanceof HTMLInputElement || targetInput instanceof HTMLTextAreaElement)) {
        return;
    }

    targetInput.value = translatedValue;
    targetInput.dispatchEvent(new Event('input', { bubbles: true }));

    const richTextComponent = findRichTextEditorComponent(targetInput);
    if (richTextComponent) {
        richTextComponent.applyContent(translatedValue);
    }
};

function blockInstanceBuilder(blockTypes, languages, entryOptionsUrl = '', translateUrl = '', defaultLangCode = 'ES') {
    const configFactory = typeof window.blockInstanceConfigFactory === 'function'
        ? window.blockInstanceConfigFactory(entryOptionsUrl, {})
        : {};

    return {
        ...configFactory,
        blockTypes,
        languages,
        entryOptionsUrl,
        translateUrl,
        defaultLangCode,
        selectedBlockType: null,
        activeLangId: null,
        contentFields: {},
        configFields: {},
        blockTypeSearch: '',

        // Filters the Paso 1 catalog by name/block_key/category — 30+ ungrouped
        // cards was hard to scan for a specific block type.
        filteredBlockTypes() {
            const q = this.blockTypeSearch.trim().toLowerCase();
            if (q === '') return this.blockTypes;
            return this.blockTypes.filter(bt => {
                const haystack = [bt.name, bt.block_key, bt.category, bt.description]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
                return haystack.includes(q);
            });
        },

        translating: false,
        translatingAll: false,
        translateError: '',
        translateAllProgress: '',

        // Repeater state: keyed by `${langId}_${fieldKey}`
        repeaterItems: {},

        // Picked file metadata keyed by `${langId}_${fieldKey}` (top-level file fields)
        pickedFilesMap: {},

        init() {
            const def = this.languages.find(l => l.is_default == 1);
            this.activeLangId = def ? def.id : (this.languages[0]?.id || null);
            if (typeof configFactory.init === 'function') {
                configFactory.init.call(this);
            }
        },

        selectBlockType(bt) {
            this.selectedBlockType = bt;
            const schema = bt.schema_definition || {};
            this.contentFields = schema.fields       || {};
            this.configFields  = schema.config_fields || {};
            this.repeaterItems = {};
            this.pickedFilesMap = {};
            if (typeof this.setDefaultsFromFields === 'function') {
                this.setDefaultsFromFields(this.configFields || {});
            }
            if (typeof lucide !== 'undefined') { setTimeout(() => lucide.createIcons(), 50); }
        },

        // ── Repeater helpers ─────────────────────────────────────────────────
        repeaterList(langId, fieldKey) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            return this.repeaterItems[k];
        },

        isImageAccept(accept) {
            const normalized = String(accept || '').trim().toLowerCase();
            return normalized === 'image'
                || normalized === 'image/*'
                || normalized.startsWith('image/');
        },

        normalizeMediaReferenceValue(value = {}) {
            const raw = (value && typeof value === 'object' && !Array.isArray(value)) ? value : {};
            const fileId = String(raw.file_id ?? raw.fileId ?? '');
            const url = String(raw.url ?? raw.external_url ?? '');
            let sourceKind = String(raw.source_kind ?? raw.sourceKind ?? '');

            if (!sourceKind) {
                sourceKind = fileId !== '' || /\/files\/\d+\/(?:view|download)(?:\?.*)?$/i.test(url)
                    ? 'hub_file'
                    : (url !== '' ? 'external_url' : 'hub_file');
            }

            return {
                source_kind: sourceKind,
                file_id: sourceKind === 'external_url' ? '' : fileId,
                url,
            };
        },

        normalizeRepeaterItem(itemFields, item = {}) {
            const normalized = {};
            Object.keys(itemFields || {}).forEach(subKey => {
                const subField = itemFields[subKey] || {};
                if (subField.type === 'media_reference' || (subField.type === 'file' && this.isImageAccept(subField.accept))) {
                    normalized[subKey] = this.normalizeMediaReferenceValue(
                        item[subKey] || {
                            source_kind: item[subKey + '_source_kind'] || '',
                            file_id: item[subKey + '_file_id'] || '',
                            url: item[subKey + '_url'] || '',
                        }
                    );
                } else if (subField.type === 'file') {
                    normalized[subKey + '_file_id'] = String(item[subKey + '_file_id'] || '');
                    normalized[subKey + '_preview_url'] = '';
                    normalized[subKey + '_url'] = String(item[subKey + '_url'] || '');
                } else {
                    normalized[subKey] = item[subKey] ?? '';
                }
            });

            return normalized;
        },

        addItem(langId, fieldKey, itemFields) {
            const k = `${langId}_${fieldKey}`;
            if (!this.repeaterItems[k]) this.repeaterItems[k] = [];
            const item = this.normalizeRepeaterItem(itemFields, {});
            this.repeaterItems[k].push(item);
        },

        removeItem(langId, fieldKey, idx) {
            const k = `${langId}_${fieldKey}`;
            if (this.repeaterItems[k]) this.repeaterItems[k].splice(idx, 1);
        },

        // ── File picker helpers ───────────────────────────────────────────────
        getPickedFileId(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).id || '';
        },

        getPickedFileUrl(langId, fieldKey) {
            return (this.pickedFilesMap[`${langId}_${fieldKey}`] || {}).url || '';
        },

        openFilePicker(callback, accept) {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[accept] ?? 'image';
            const mimeAccept = (!accept || accept === 'any') ? ''
                : accept.includes('/') ? accept
                : accept + '/*';
            Alpine.store('filePicker').show({
                filterType,
                accept: mimeAccept,
                multi: false,
                onSelect: (file) => callback(file),
            });
        },

        // pickFile is called by the openFilePicker callback.
        // For top-level file fields: langId, fieldKey, itemIdx=null, subKey=null
        // For repeater sub-fields: all four are set
        pickFile(langId, fieldKey, itemIdx, subKey, file) {
            if (itemIdx === null) {
                this.pickedFilesMap[`${langId}_${fieldKey}`] = { id: file.id, url: file.url, preview_url: window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : file.url };
            } else {
                const k = `${langId}_${fieldKey}`;
                if (this.repeaterItems[k] && this.repeaterItems[k][itemIdx]) {
                    this.repeaterItems[k][itemIdx][subKey + '_file_id']     = file.id;
                    this.repeaterItems[k][itemIdx][subKey + '_url']         = file.url;
                    this.repeaterItems[k][itemIdx][subKey + '_preview_url'] = window.bestFilePreviewUrl ? window.bestFilePreviewUrl(file) : file.url;
                }
            }
        },

        clearPickedFile(langId, fieldKey) {
            this.pickedFilesMap[`${langId}_${fieldKey}`] = { id: '', url: '', preview_url: '' };
        },

        copyFileToAllLanguages(sourceLangId, fieldKey) {
            const sourceFile = this.pickedFilesMap[`${sourceLangId}_${fieldKey}`];
            if (!sourceFile || !sourceFile.id) return;

            const updatedMap = { ...this.pickedFilesMap };
            this.languages.forEach(lang => {
                if (Number(lang.id) !== Number(sourceLangId)) {
                    updatedMap[`${lang.id}_${fieldKey}`] = {
                        id: sourceFile.id,
                        url: sourceFile.url,
                        preview_url: sourceFile.preview_url
                    };
                }
            });
            this.pickedFilesMap = updatedMap;
        },

        getTranslateTargets() {
            if (!this.selectedBlockType) return [];

            const defLang = this.languages.find(l => l.is_default == 1);
            if (!defLang) return [];

            const defLangIndex = this.languages.findIndex(l => l.is_default == 1);

            const translatableFieldKeys = [];
            Object.entries(this.contentFields).forEach(([fieldKey, field]) => {
                const fieldType = field.type || 'string';
                if (!['file', 'media_reference', 'repeater', 'boolean', 'integer', 'select'].includes(fieldType)) {
                    translatableFieldKeys.push(fieldKey);
                }
            });

            if (translatableFieldKeys.length === 0) return [];

            const targets = [];
            this.languages.forEach((lang, idx) => {
                if (idx === defLangIndex) return;

                const fieldPairs = [];
                translatableFieldKeys.forEach(fieldKey => {
                    fieldPairs.push({
                        from: `[name="translations[${defLangIndex}][block_data][${fieldKey}]"]`,
                        to: `[name="translations[${idx}][block_data][${fieldKey}]"]`
                    });
                });

                targets.push({
                    langCode: lang.code.toUpperCase(),
                    fieldPairs: fieldPairs
                });
            });

            return targets;
        },

        async _translatePairs(targetLangCode, fieldPairs) {
            for (const pair of fieldPairs) {
                const sourceEl = document.querySelector(pair.from);
                const targetEl = document.querySelector(pair.to);
                if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) continue;
                if (!(targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement)) continue;
                const sourceText = sourceEl.value.trim();
                if (sourceText === '') continue;

                const url = new URL(this.translateUrl, window.location.origin);
                url.searchParams.set('text', sourceText);
                url.searchParams.set('source_lang', this.defaultLangCode.toUpperCase());
                url.searchParams.set('target_lang', targetLangCode.toUpperCase());

                const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                const json = await res.json();
                if (json && typeof json.translated === 'string') {
                    applyTranslatedText(targetEl, json.translated);
                } else if (json && json.error) {
                    throw new Error(json.error);
                }
            }
        },

        async autoTranslateAll() {
            const targets = this.getTranslateTargets();
            if (this.translateUrl === '' || this.translating || this.translatingAll || targets.length === 0) return;

            this.translatingAll = true;
            this.translateError = '';
            try {
                for (let i = 0; i < targets.length; i++) {
                    const { langCode, fieldPairs } = targets[i];
                    this.translateAllProgress = langCode + ' (' + (i + 1) + '/' + targets.length + ')';
                    await this._translatePairs(langCode, fieldPairs);
                }
                this.translateAllProgress = '';
            } catch (e) {
                this.translateError = e instanceof Error ? e.message : String(e);
                this.translateAllProgress = '';
            } finally {
                this.translatingAll = false;
            }
        },

        openPreview() {
            if (!this.selectedBlockType) return;
            const form = this.$root instanceof HTMLElement ? this.$root.querySelector('form') : null;
            const payload = typeof window.formValuesToObject === 'function'
                ? window.formValuesToObject(form)
                : {};
            const config = payload.block_config || {};
            window.dispatchEvent(new CustomEvent('block-preview-open', {
                detail: { blockKey: this.selectedBlockType.block_key, blockConfig: config, blockData: {}, previewMode: 'live' },
            }));
        },
    };
}
</script>
