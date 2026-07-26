<?php
$templates    = $templates ?? [];
$sourceKinds  = $sourceKinds ?? [];
$templatesJs  = json_encode($templates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$sourceKindsJs = json_encode($sourceKinds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$previewUrl   = route_to('admin.cms.blocks.preview');
?>
<meta name="block-preview-url" content="<?= esc($previewUrl) ?>">

<div class="mb-4">
    <a href="<?= route_to('admin.cms.block_types') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<div x-data="blockTypeDesigner(<?= esc($templatesJs, 'attr') ?>, <?= esc($sourceKindsJs, 'attr') ?>)" class="space-y-6">
    <?php ob_start(); ?>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700"><?= esc(lang('BlockTypes.source_title')) ?></p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900"><?= esc(lang('BlockTypes.source_desc')) ?></h3>
                    <p class="mt-1 text-sm text-gray-500"><?= esc(lang('BlockTypes.source_filter_hint')) ?></p>
                </div>
                <button type="button"
                    @click="enableCustomMode()"
                    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:border-brand-300 hover:bg-brand-50">
                    <?= esc(lang('BlockTypes.source_custom_title')) ?>
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <template x-for="source in sourceKinds" :key="source.key">
                    <button type="button"
                        @click="selectSource(source)"
                        :class="isSourceSelected(source)
                            ? 'border-brand-600 bg-brand-50 ring-2 ring-brand-400'
                            : 'border-gray-200 bg-white hover:border-brand-400 hover:bg-brand-50/30'"
                        class="flex flex-col items-start gap-2 rounded-xl border-2 p-4 text-left transition-all">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600"
                              :class="isSourceSelected(source) ? 'bg-brand-100 text-brand-700' : ''">
                            <svg x-show="source.key === 'manual'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h10"/></svg>
                            <svg x-show="source.key === 'page'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h10l4 4v14H3V3h4z"/></svg>
                            <svg x-show="source.key === 'collection'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                            <svg x-show="source.key === 'entry'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4h10v16H7zM9 8h6M9 12h6M9 16h4"/></svg>
                            <svg x-show="source.key === 'container'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4zM8 9h8M8 13h8"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-900" x-text="source.label"></span>
                        <span class="text-xs text-gray-500" x-text="source.description"></span>
                    </button>
                </template>
            </div>
        </div>
    </section>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-700"><?= esc(lang('BlockTypes.source_template_title')) ?></p>
                <h3 class="mt-1 text-lg font-semibold text-gray-900"><?= esc(lang('BlockTypes.source_template_desc')) ?></h3>
            </div>
            <button type="button"
                @click="openPreview()"
                class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                Vista previa
            </button>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <template x-for="tpl in visibleTemplates" :key="tpl.key">
                <button type="button"
                    @click="selectTemplate(tpl)"
                    :class="isSelected(tpl)
                        ? 'border-brand-600 bg-brand-50 ring-2 ring-brand-400'
                        : 'border-gray-200 bg-white hover:border-brand-400 hover:bg-brand-50/30'"
                    class="relative flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-center transition-all">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500"
                         :class="isSelected(tpl) ? 'bg-brand-100 text-brand-700' : ''">
                        <i :data-lucide="tpl.icon" class="h-5 w-5"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-800 leading-tight" x-text="tpl.name"></span>
                    <code class="font-mono text-[10px] text-gray-400" x-text="tpl.key"></code>
                    <span x-show="isSelected(tpl)"
                          class="absolute right-2 top-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-brand-600">
                        <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M13.485 1.431a1.473 1.473 0 0 1 2.104 0 1.473 1.473 0 0 1 0 2.104L6.555 12.64 1.127 7.212a1.473 1.473 0 0 1 0-2.104 1.474 1.474 0 0 1 2.104 0l3.324 3.324 6.93-6.94z"/>
                        </svg>
                    </span>
                </button>
            </template>

            <button type="button"
                @click="enableCustomMode()"
                :class="customMode
                    ? 'border-gray-600 bg-gray-50 ring-2 ring-gray-400'
                    : 'border-dashed border-gray-300 hover:border-gray-500 hover:bg-gray-50'"
                class="flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-center transition-all">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-gray-600"><?= esc(lang('BlockTypes.source_custom_title')) ?></span>
                <code class="font-mono text-[10px] text-gray-400">custom</code>
            </button>
        </div>

        <div x-show="selectedTemplate || customMode" x-cloak class="mt-4 rounded-lg border border-brand-200 bg-brand-50 p-3 text-sm text-brand-800">
            <span class="font-medium" x-text="selectedTemplate?.name || '<?= esc(lang('BlockTypes.source_custom_title'), 'js') ?>'"></span>
            <span class="mx-1">·</span>
            <span x-text="selectedTemplate?.description || '<?= esc(lang('BlockTypes.source_custom_desc'), 'js') ?>'"></span>
        </div>
    </section>
    <?php $step1Content = ob_get_clean(); ?>

    <?= view('components/display/form_section', [
        'title' => 'BlockTypes.source_title',
        'description' => 'BlockTypes.source_desc',
        'content' => $step1Content,
        'bodyClass' => 'space-y-4'
    ]) ?>

    <?php ob_start(); ?>
    <section x-show="selectedTemplate || customMode" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-end mb-5">
            <button type="button"
                @click="openPreview()"
                class="flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                Vista previa
            </button>
        </div>

        <form method="post" action="<?= route_to('admin.cms.block_types.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3"
              @submit="rebuildJson()">
            <?= csrf_field() ?>

            <!-- Single hidden input that always carries the effective block_key -->
            <input type="hidden" name="block_key" :value="effectiveBlockKey">

            <div class="space-y-6 lg:col-span-2">
            <!-- Visible text input only in custom mode — no name attr, bound to customBlockKey -->
            <div x-show="customMode" x-cloak class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">
                    <?= esc(lang('BlockTypes.field_block_key')) ?> <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       x-model="customBlockKey"
                       placeholder="<?= esc(lang('BlockTypes.field_block_key_placeholder')) ?>"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <p class="text-xs text-gray-500"><?= esc(lang('BlockTypes.field_block_key_help')) ?></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?= view('components/form/text', [
                    'name' => 'name',
                    'label' => 'BlockTypes.field_name',
                    'required' => true,
                    'value' => '',
                    'placeholder' => 'BlockTypes.field_name_placeholder',
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'category',
                    'label' => 'BlockTypes.field_category',
                    'required' => true,
                    'value' => '',
                    'placeholder' => 'BlockTypes.field_category_placeholder',
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <!-- Schema: hidden JSON + editor estructurado -->
            <input type="hidden" name="schema_definition" :value="schemaJson">

            <!-- Campos de Contenido -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800">Campos de Contenido</h4>
                        <p class="text-xs text-gray-500">Datos traducibles que el editor llena por idioma.</p>
                    </div>
                    <button type="button" @click="addField('content')" class="text-xs btn-secondary py-1 px-3">+ Añadir campo</button>
                </div>

                <div class="space-y-2">
                    <template x-for="(field, index) in schemaFields" :key="index">
                        <?= view('cms/block_types/partials/schema_field_row', ['section' => 'content']) ?>
                    </template>
                    <p x-show="schemaFields.length === 0" class="text-xs text-gray-400 italic py-2 text-center border border-dashed border-gray-200 rounded-lg">
                        Sin campos de contenido. Este bloque no tiene texto editable.
                    </p>
                </div>
            </div>

            <!-- Campos de Configuración -->
            <div class="border-t border-gray-100 pt-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800">Campos de Configuración</h4>
                        <p class="text-xs text-gray-500">Ajustes estructurales no traducibles (CSS, variante de layout, etc.).</p>
                    </div>
                    <button type="button" @click="addField('config')" class="text-xs btn-secondary py-1 px-3">+ Añadir campo</button>
                </div>

                <div class="space-y-2">
                    <template x-for="(field, index) in configFields" :key="index">
                        <?= view('cms/block_types/partials/schema_field_row', ['section' => 'config']) ?>
                    </template>
                    <p x-show="configFields.length === 0" class="text-xs text-gray-400 italic py-2 text-center border border-dashed border-gray-200 rounded-lg">
                        Sin campos de configuración.
                    </p>
                </div>
            </div>

            <!-- Opciones avanzadas -->
            <details class="group border border-gray-200 rounded-lg">
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                    <span>Opciones avanzadas</span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                    <?= view('components/form/textarea', ['name' => 'description', 'label' => 'BlockTypes.field_description', 'required' => false, 'value' => '', 'rows' => 2, 'errors' => $errors ?? []]) ?>
                    <?= view('components/form/text', ['name' => 'icon',        'label' => 'BlockTypes.field_icon',        'required' => false, 'value' => '', 'errors' => $errors ?? []]) ?>
                    <?= view('components/form/boolean', ['name' => 'supports_pages',   'label' => 'BlockTypes.field_supports_pages',   'value' => true,  'on_label' => 'App.yes', 'off_label' => 'App.no', 'errors' => $errors ?? []]) ?>
                    <?= view('components/form/boolean', ['name' => 'supports_entries', 'label' => 'BlockTypes.field_supports_entries', 'value' => true,  'on_label' => 'App.yes', 'off_label' => 'App.no', 'errors' => $errors ?? []]) ?>
                    <div>
                        <span class="block text-sm font-medium text-gray-700">
                            <?= lang('BlockTypes.field_is_container') ?>
                        </span>
                        <input type="hidden" name="is_container" value="0">
                        <label class="mt-2 inline-flex cursor-pointer items-center gap-3">
                            <input id="is_container" name="is_container" type="checkbox" value="1"
                                   class="peer sr-only" x-model="isContainer" @change="rebuildJson()">
                            <span class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-gray-200 transition-colors duration-200 ease-in-out peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2"
                                  :style="isContainer ? 'width: 2.75rem; height: 1.5rem; background-color: var(--color-brand-600)' : 'width: 2.75rem; height: 1.5rem'"
                                  aria-hidden="true">
                                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 ease-in-out"
                                      :style="isContainer ? 'transform: translateX(1.25rem)' : 'transform: translateX(0.125rem)'"></span>
                            </span>
                            <span class="text-sm font-medium text-gray-700" x-text="isContainer ? 'Sí' : 'No'"></span>
                        </label>
                    </div>

                    <!-- Seleccion de bloques hijos permitidos -->
                    <div x-show="isContainer" x-cloak class="mt-4 p-4 border border-brand-200 bg-brand-50/30 rounded-xl space-y-3">
                        <h5 class="text-sm font-semibold text-brand-900">Bloques Hijos Permitidos</h5>
                        <p class="text-xs text-brand-700">Selecciona qué tipos de bloques se pueden agregar dentro de este contenedor.</p>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <?php foreach ($blockTypes as $bt): ?>
                                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                    <input type="checkbox" value="<?= esc($bt['block_key']) ?>" 
                                           x-model="allowedChildren" @change="rebuildJson()"
                                           class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    <span><?= esc($bt['name']) ?> (<code class="font-mono text-[10px]"><?= esc($bt['block_key']) ?></code>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?= view('components/form/boolean', ['name' => 'is_active', 'label' => 'BlockTypes.field_is_active', 'value' => true, 'on_label' => 'BlockTypes.field_is_active_on', 'off_label' => 'BlockTypes.field_is_active_off', 'errors' => $errors ?? []]) ?>
                    <?= view('components/form/text', ['name' => 'sort_order', 'label' => 'BlockTypes.field_sort_order', 'required' => false, 'value' => '0', 'errors' => $errors ?? []]) ?>
                </div>
            </details>
            </div>

            <aside class="space-y-6">
                <?= view('components/display/admin_actions_panel', [
                    'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                        . '<a href="' . esc(route_to('admin.cms.block_types'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
                ]) ?>
            </aside>
        </form>
    </section>
    <?php $step2Content = ob_get_clean(); ?>

    <?= view('components/display/form_section', [
        'title' => 'BlockTypes.step2_title',
        'description' => 'BlockTypes.step2_desc',
        'content' => $step2Content,
        'bodyClass' => 'space-y-6'
    ]) ?>

</div>
