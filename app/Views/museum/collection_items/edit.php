<?php $item = $item ?? []; ?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.museum.collection_items'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Museum.collection_items_title',
    'title' => 'Museum.collection_items_edit',
]) ?>

<form id="delete-item-form" method="post" action="<?= route_to('admin.museum.collection_items.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['title'] ?? $item['id'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>

<form method="post" action="<?= route_to('admin.museum.collection_items.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2 space-y-6">
        <!-- Core classification section -->
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/text', [
                    'name' => 'inventory_code',
                    'label' => 'Museum.field_inventory_code',
                    'required' => true,
                    'value' => old('inventory_code', $item['inventory_code'] ?? ''),
                    'placeholder' => 'Museum.field_inventory_code_placeholder',
                    'help' => 'Museum.field_inventory_code_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/relation', [
                    'name' => 'category_id',
                    'label' => 'Museum.field_category_id',
                    'required' => true,
                    'options' => $categories ?? [],
                    'placeholder' => 'Museum.field_category_id_placeholder',
                    'help' => 'Museum.field_category_id_help',
                    'value' => old('category_id', $item['category_id'] ?? ''),
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/select', [
                    'name' => 'status',
                    'label' => 'Museum.field_status',
                    'required' => true,
                    'options' => [
                        'draft' => lang('Pages.status_draft'),
                        'published' => lang('Pages.status_published'),
                        'archived' => lang('Pages.status_archived')
                    ],
                    'value' => old('status', $item['status'] ?? 'published'),
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'collection_number',
                    'label' => 'Museum.field_collection_number',
                    'required' => false,
                    'value' => old('collection_number', $item['collection_number'] ?? ''),
                    'placeholder' => 'Museum.field_collection_number_placeholder',
                    'help' => 'Museum.field_collection_number_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/text', [
                    'name' => 'origin',
                    'label' => 'Museum.field_origin',
                    'required' => false,
                    'value' => old('origin', $item['origin'] ?? ''),
                    'placeholder' => 'Museum.field_origin_placeholder',
                    'help' => 'Museum.field_origin_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'period',
                    'label' => 'Museum.field_period',
                    'required' => false,
                    'value' => old('period', $item['period'] ?? ''),
                    'placeholder' => 'Museum.field_period_placeholder',
                    'help' => 'Museum.field_period_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/text', [
                    'name' => 'creator',
                    'label' => 'Museum.field_creator',
                    'required' => false,
                    'value' => old('creator', $item['creator'] ?? ''),
                    'placeholder' => 'Museum.field_creator_placeholder',
                    'help' => 'Museum.field_creator_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'dimensions',
                    'label' => 'Museum.field_dimensions',
                    'required' => false,
                    'value' => old('dimensions', $item['dimensions'] ?? ''),
                    'placeholder' => 'Museum.field_dimensions_placeholder',
                    'help' => 'Museum.field_dimensions_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </section>

        <!-- Multilingual Translations with language tabs -->
        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId    = (int) ($defaultLangId ?? 0);
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $defaultLangCode  = (string) ($defaultLangCode ?? '');
            $translateUrl     = route_to('admin.cms.translate');
            $translations     = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            ?>
            <input type="hidden" name="default_language_id" value="<?= esc((string) $defaultLangId) ?>">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Pages.translations_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Pages.translations_help')) ?></p>
                </div>

                <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <!-- Tab bar + translate-all button -->
                    <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                        <div class="flex gap-0.5" role="tablist">
                            <?php foreach ($languages as $lang): ?>
                                <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                    <?= esc(strtoupper($lang['code'])) ?>
                                    <?php if (!empty($lang['is_default'])): ?>
                                        <span class="ml-1 text-brand-400">★</span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($translateTargets)): ?>
                        <button type="button"
                            @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                            :disabled="translating || translatingAll"
                            class="mb-px inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                            <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                            <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Translate error message -->
                    <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                    <!-- Tab panels -->
                    <?php foreach ($languages as $index => $lang): ?>
                        <?php
                        $isDefault = !empty($lang['is_default']);
                        $langCode  = strtoupper($lang['code'] ?? '');
                        $transValue = [];
                        foreach ($translations as $translation) {
                            if (is_array($translation)) {
                                $loc = strtolower((string) ($translation['locale'] ?? $translation['language_code'] ?? ''));
                                $targetLoc = strtolower((string) ($lang['code'] ?? ''));
                                if (($loc !== '' && $loc === $targetLoc) || (int) ($translation['language_id'] ?? 0) === (int) $lang['id']) {
                                    $transValue = $translation;
                                    break;
                                }
                            }
                        }
                        if (empty($transValue) && $isDefault) {
                            $transValue = [
                                'name' => $item['name'] ?? '',
                                'summary' => $item['summary'] ?? '',
                                'curiosidad' => $item['curiosidad'] ?? '',
                                'contenido' => $item['contenido'] ?? '',
                                'physical_description' => $item['physical_description'] ?? '',
                                'ubicacion' => $item['ubicacion'] ?? '',
                            ];
                        }
                        ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][locale]" value="<?= esc($lang['code']) ?>">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][name]",
                                'label' => 'Museum.field_name',
                                'required' => $isDefault,
                                'placeholder' => 'Museum.field_name_placeholder',
                                'help' => 'Museum.field_name_help',
                                'value' => old("translations.{$index}.name", $transValue['name'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][summary]",
                                'label' => 'Museum.field_summary',
                                'required' => false,
                                'placeholder' => 'Museum.field_summary_placeholder',
                                'help' => 'Museum.field_summary_help',
                                'value' => old("translations.{$index}.summary", $transValue['summary'] ?? ''),
                                'rows' => 3,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][curiosidad]",
                                'label' => 'Museum.field_curiosidad',
                                'required' => false,
                                'placeholder' => 'Museum.field_curiosidad_placeholder',
                                'help' => 'Museum.field_curiosidad_help',
                                'value' => old("translations.{$index}.curiosidad", $transValue['curiosidad'] ?? ''),
                                'rows' => 2,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][contenido]",
                                'label' => 'Museum.field_contenido',
                                'required' => false,
                                'placeholder' => 'Museum.field_contenido_placeholder',
                                'help' => 'Museum.field_contenido_help',
                                'value' => old("translations.{$index}.contenido", $transValue['contenido'] ?? ''),
                                'rows' => 4,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][physical_description]",
                                'label' => 'Museum.field_physical_description',
                                'required' => false,
                                'placeholder' => 'Museum.field_physical_description_placeholder',
                                'help' => 'Museum.field_physical_description_help',
                                'value' => old("translations.{$index}.physical_description", $transValue['physical_description'] ?? ''),
                                'rows' => 2,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][ubicacion]",
                                'label' => 'Museum.field_ubicacion',
                                'required' => false,
                                'placeholder' => 'Museum.field_ubicacion_placeholder',
                                'help' => 'Museum.field_ubicacion_help',
                                'value' => old("translations.{$index}.ubicacion", $transValue['ubicacion'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Additional metadata -->
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_additional')) ?></h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/file', [
                    'name' => 'cover_file_id',
                    'label' => 'Museum.field_cover_file_id',
                    'required' => false,
                    'value' => old('cover_file_id', $item['cover_file_id'] ?? ''),
                    'accept' => 'image/*',
                    'filterType' => 'image',
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'collection_group',
                    'label' => 'Museum.field_collection_group',
                    'required' => false,
                    'value' => old('collection_group', $item['collection_group'] ?? ''),
                    'placeholder' => 'Museum.field_collection_group_placeholder',
                    'help' => 'Museum.field_collection_group_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <?= view('components/form/file_gallery', [
                'name' => 'gallery_file_ids',
                'label' => 'Museum.field_gallery_file_ids',
                'value' => old('gallery_file_ids', $item['gallery_file_ids'] ?? ''),
                'accept' => 'image/*',
                'filterType' => 'image',
                'help' => 'Museum.field_gallery_file_ids_help',
                'errors' => $errors ?? []
            ]) ?>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/text', [
                    'name' => 'ingress_type',
                    'label' => 'Museum.field_ingress_type',
                    'required' => false,
                    'value' => old('ingress_type', $item['ingress_type'] ?? ''),
                    'placeholder' => 'Museum.field_ingress_type_placeholder',
                    'help' => 'Museum.field_ingress_type_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'donated_by',
                    'label' => 'Museum.field_donated_by',
                    'required' => false,
                    'value' => old('donated_by', $item['donated_by'] ?? ''),
                    'placeholder' => 'Museum.field_donated_by_placeholder',
                    'help' => 'Museum.field_donated_by_help',
                    'maxlength' => 255,
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <?= view('components/form/textarea', [
                'name' => 'physical_description',
                'label' => 'Museum.field_physical_description',
                'required' => false,
                'value' => old('physical_description', $item['physical_description'] ?? ''),
                'placeholder' => 'Museum.field_physical_description_placeholder',
                'help' => 'Museum.field_physical_description_help',
                'errors' => $errors ?? []
            ]) ?>

            <?= view('components/form/textarea', [
                'name' => 'tags',
                'label' => 'Museum.field_tags',
                'required' => false,
                'value' => old('tags', $item['tags'] ?? ''),
                'placeholder' => 'Museum.field_tags_placeholder',
                'help' => 'Museum.field_tags_help',
                'errors' => $errors ?? []
            ]) ?>

            <?= view('components/form/textarea', [
                'name' => 'links',
                'label' => 'Museum.field_links',
                'required' => false,
                'value' => old('links', $item['links'] ?? ''),
                'placeholder' => 'Museum.field_links_placeholder',
                'help' => 'Museum.field_links_help',
                'errors' => $errors ?? []
            ]) ?>

            <?= view('components/form/textarea', [
                'name' => 'company_history',
                'label' => 'Museum.field_company_history',
                'required' => false,
                'value' => old('company_history', $item['company_history'] ?? ''),
                'placeholder' => 'Museum.field_company_history_placeholder',
                'help' => 'Museum.field_company_history_help',
                'errors' => $errors ?? []
            ]) ?>
        </section>

        <!-- Materials & Notes -->
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <?= view('components/form/textarea', [
                'name' => 'materials',
                'label' => 'Museum.field_materials',
                'required' => false,
                'value' => old('materials', $item['materials'] ?? ''),
                'placeholder' => 'Museum.field_materials_placeholder',
                'help' => 'Museum.field_materials_help',
                'errors' => $errors ?? []
            ]) ?>

            <?= view('components/form/textarea', [
                'name' => 'internal_notes',
                'label' => 'Museum.field_internal_notes',
                'required' => false,
                'value' => old('internal_notes', $item['internal_notes'] ?? ''),
                'placeholder' => 'Museum.field_internal_notes_placeholder',
                'help' => 'Museum.field_internal_notes_help',
                'errors' => $errors ?? []
            ]) ?>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . ' w-full justify-center text-center py-2.5">' . esc(lang('App.save')) . '</button>'
                . '<button type="submit" form="delete-item-form" class="' . esc(action_button_class('danger'), 'attr') . ' w-full justify-center text-center py-2.5 mt-2">' . esc(lang('App.delete')) . '</button>'
                . '<a href="' . esc(route_to('admin.museum.collection_items'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . ' w-full justify-center text-center py-2.5 mt-2">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <?= view('components/form/boolean', [
                'name' => 'show_in_totem',
                'label' => 'Museum.field_show_in_totem',
                'value' => old('show_in_totem', !empty($item['show_in_totem'])),
                'on_label' => 'Museum.field_show_in_totem_on',
                'off_label' => 'Museum.field_show_in_totem_off',
                'help' => 'Museum.field_show_in_totem_help',
                'errors' => $errors ?? []
            ]) ?>

            <?= view('components/form/boolean', [
                'name' => 'is_active',
                'label' => 'Museum.field_is_active',
                'value' => old('is_active', !empty($item['is_active'])),
                'on_label' => 'Museum.field_is_active_on',
                'off_label' => 'Museum.field_is_active_off',
                'help' => 'Museum.field_is_active_help',
                'errors' => $errors ?? []
            ]) ?>
        </div>
    </aside>
</form>
