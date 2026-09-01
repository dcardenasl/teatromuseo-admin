<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.museum.categories'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Museum.categories_title',
    'title' => 'Museum.categories_create',
]) ?>

<form method="post" action="<?= route_to('admin.museum.categories.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2 space-y-6">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/slug', [
                    'name' => 'slug',
                    'label' => 'Museum.field_slug',
                    'required' => true,
                    'sourceId' => sprintf('[name="translations[%d][name]"]', $defaultLangIndex ?? 0),
                    'value' => old('slug', ''),
                    'errors' => $errors ?? [],
                    'help' => 'Museum.field_slug_help',
                ]) ?>

                <?= view('components/form/text', [
                    'name' => 'icon',
                    'label' => 'Museum.field_icon',
                    'required' => false,
                    'value' => old('icon', ''),
                    'placeholder' => 'Museum.field_icon_placeholder',
                    'help' => 'Museum.field_icon_help',
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
            ?>
            <input type="hidden" name="default_language_id" value="<?= esc((string) $defaultLangId) ?>">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('ContentTranslations.translations_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('ContentTranslations.translations_help')) ?></p>
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
                                'maxlength' => 255,
                                'value' => old("translations.{$index}.name", ''),
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][short_description]",
                                'label' => 'Museum.field_short_description',
                                'required' => false,
                                'placeholder' => 'Museum.field_short_description_placeholder',
                                'help' => 'Museum.field_short_description_help',
                                'value' => old("translations.{$index}.short_description", ''),
                                'rows' => 3,
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . ' w-full justify-center text-center py-2.5">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.museum.categories'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . ' w-full justify-center text-center py-2.5 mt-2">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
