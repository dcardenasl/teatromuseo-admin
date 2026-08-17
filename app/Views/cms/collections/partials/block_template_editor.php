<?php
/**
 * Structured block template builder used by collection create/edit forms.
 *
 * Variables:
 *   $value       mixed  Existing template value (array or JSON string)
 *   $blockTypes  array  Active block types from the CMS catalog
 *   $errors      array  Validation errors keyed by field name
 */

$value = $value ?? '';
$errors = $errors ?? [];
$blockTypes = $blockTypes ?? [];

$availableBlockTypes = [];
foreach ((array) $blockTypes as $blockType) {
    if (! is_array($blockType) || empty($blockType['block_key'])) {
        continue;
    }

    $availableBlockTypes[] = [
        'id' => (int) ($blockType['id'] ?? 0),
        'block_key' => (string) ($blockType['block_key'] ?? ''),
        'name' => (string) ($blockType['name'] ?? $blockType['block_key'] ?? ''),
        'description' => (string) ($blockType['description'] ?? ''),
        'icon' => (string) ($blockType['icon'] ?? 'layout-template'),
    ];
}

$blockTypeByKey = [];
foreach ($availableBlockTypes as $blockType) {
    $blockTypeByKey[$blockType['block_key']] = $blockType;
}

$toBoolean = static function (mixed $value, bool $default = false): bool {
    if ($value === null || $value === '') {
        return $default;
    }

    if (is_bool($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (int) $value !== 0;
    }

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }
    }

    return (bool) $value;
};

$toInteger = static function (mixed $value, int $default): int {
    if ($value === null || $value === '') {
        return $default;
    }

    if (is_int($value)) {
        return $value;
    }

    if (is_float($value)) {
        return (int) $value;
    }

    if (is_string($value) && is_numeric($value)) {
        return (int) $value;
    }

    return $default;
};

$rawTemplate = old('block_template', $value);
$template = null;
if (is_array($rawTemplate)) {
    $template = $rawTemplate;
} elseif (is_string($rawTemplate) && trim($rawTemplate) !== '') {
    $decoded = json_decode($rawTemplate, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $template = $decoded;
    }
}

if (! is_array($template)) {
    $template = ['version' => '1.0', 'blocks' => []];
}

$template['version'] = (string) ($template['version'] ?? '1.0');
$templateBlocks = is_array($template['blocks'] ?? null) ? $template['blocks'] : [];

$normalizeDefaultValue = static function (mixed $value): array {
    if (is_bool($value)) {
        return ['type' => 'boolean', 'value' => $value ? '1' : '0'];
    }

    if (is_int($value) || is_float($value)) {
        return ['type' => 'number', 'value' => (string) $value];
    }

    if ($value === null) {
        return ['type' => 'string', 'value' => ''];
    }

    if (is_scalar($value)) {
        return ['type' => 'string', 'value' => (string) $value];
    }

    return ['type' => 'string', 'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''];
};

$initialRows = [];
foreach ($templateBlocks as $index => $block) {
    if (! is_array($block)) {
        continue;
    }

    $blockKey = (string) ($block['block_key'] ?? '');
    if ($blockKey === '') {
        continue;
    }

    $resolvedType = $blockTypeByKey[$blockKey] ?? null;
    $defaults = [];
    if (isset($block['block_config_defaults']) && is_array($block['block_config_defaults'])) {
        foreach ($block['block_config_defaults'] as $defaultKey => $defaultValue) {
            $normalized = $normalizeDefaultValue($defaultValue);
            $defaults[] = [
                'key' => (string) $defaultKey,
                'type' => $normalized['type'],
                'value' => $normalized['value'],
            ];
        }
    }

    $initialRows[] = [
        'block_key' => $blockKey,
        'label' => (string) ($block['label'] ?? ($resolvedType['name'] ?? $blockKey)),
        'help_text' => (string) ($block['help_text'] ?? ($resolvedType['description'] ?? '')),
        'sort_order' => $toInteger($block['sort_order'] ?? null, $index + 1),
        'required' => $toBoolean($block['required'] ?? null, true),
        'locked' => $toBoolean($block['locked'] ?? null, false),
        'defaults' => $defaults,
    ];
}

$initialJson = json_encode([
    'version' => '1.0',
    'blocks' => $initialRows,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

if ($initialJson === false) {
    $initialJson = '{"version":"1.0","blocks":[]}';
}

$blockTypesJson = json_encode($availableBlockTypes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($blockTypesJson === false) {
    $blockTypesJson = '[]';
}

$collectionPresets = $collectionPresets ?? [];
$collectionPresetsJson = json_encode($collectionPresets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($collectionPresetsJson === false) {
    $collectionPresetsJson = '[]';
}

$rawWizardConfig = old('wizard_config', $wizardConfig ?? null);
$initialWizardConfigJson = 'null';
if (is_array($rawWizardConfig)) {
    $initialWizardConfigJson = json_encode($rawWizardConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'null';
} elseif (is_string($rawWizardConfig) && trim($rawWizardConfig) !== '') {
    $initialWizardConfigJson = $rawWizardConfig;
}

// Fixed catalog of "native" entry-level fields the wizard-steps panel is allowed
// to arrange — mirrors App\Libraries\Cms\WizardStepFieldCatalog on the Domain,
// which rejects anything outside this list. Deliberately not open to arbitrary
// keys: any other key would only ever reach real storage by accident (see
// wizard_extra's block-schema-matching side channel).
$wizardFieldCatalog = [
    'title'            => ['type' => 'text',     'label' => lang('Collections.wizard_steps_field_title')],
    'excerpt'          => ['type' => 'textarea', 'label' => lang('Collections.wizard_steps_field_excerpt')],
    'featured_image'   => ['type' => 'image',    'label' => lang('Collections.wizard_steps_field_featured_image')],
    'meta_title'       => ['type' => 'text',     'label' => lang('Collections.wizard_steps_field_meta_title')],
    'meta_description' => ['type' => 'textarea', 'label' => lang('Collections.wizard_steps_field_meta_description')],
    'og_image'         => ['type' => 'image',    'label' => lang('Collections.wizard_steps_field_og_image')],
];
$wizardFieldCatalogJson = json_encode($wizardFieldCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
$builderUiLabelsJson = json_encode([
    'blockFallback' => lang('Collections.block_template_builder_block_fallback'),
    'presetConfirm' => lang('Collections.block_template_preset_confirm'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
?>

<div x-data="collectionBlockTemplateBuilder(<?= esc($blockTypesJson, 'attr') ?>, <?= esc($initialJson, 'attr') ?>, <?= esc($collectionPresetsJson, 'attr') ?>, <?= esc($initialWizardConfigJson, 'attr') ?>, <?= esc($wizardFieldCatalogJson, 'attr') ?>, <?= esc($builderUiLabelsJson, 'attr') ?>)" x-init="init()" class="space-y-6">
    <input type="hidden" name="block_template" x-ref="blockTemplateInput" :value="json">
    <input type="hidden" name="wizard_config" x-ref="wizardConfigInput" :value="wizardConfigJson">

    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h5 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.block_template_builder_title')) ?></h5>
                <p class="mt-1 text-xs text-gray-500">
                    <?= esc(lang('Collections.block_template_builder_overview_help')) ?>
                </p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700">
                <span><?= esc(lang('Collections.block_template_builder_count')) ?>:</span>
                <span x-text="rows.length"></span>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="button"
                @click="setActivePanel('catalog')"
                :class="activePanel === 'catalog' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-700 border-gray-300 hover:border-brand-400'"
                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors">
                <?= esc(lang('Collections.block_template_builder_catalog_tab')) ?>
            </button>
            <button type="button"
                @click="setActivePanel('structure')"
                :class="activePanel === 'structure' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-700 border-gray-300 hover:border-brand-400'"
                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors">
                <?= esc(lang('Collections.block_template_builder_structure_tab')) ?>
            </button>
            <button type="button"
                @click="setActivePanel('wizard-steps')"
                :class="activePanel === 'wizard-steps' ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-700 border-gray-300 hover:border-brand-400'"
                class="rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors">
                <?= esc(lang('Collections.wizard_steps_builder_tab')) ?>
            </button>
        </div>

        <div x-show="activePanel === 'catalog'" x-cloak class="mt-4 space-y-4">
            <div x-show="collectionPresets.length > 0" class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                <h6 class="text-xs font-semibold text-gray-900 mb-1"><?= esc(lang('Collections.block_template_preset_title')) ?></h6>
                <p class="text-xs text-gray-500 mb-3"><?= esc(lang('Collections.block_template_preset_help')) ?></p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="preset in collectionPresets" :key="preset.type_key">
                        <button type="button"
                            @click="loadPreset(preset.type_key)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:border-brand-400">
                            <i :data-lucide="preset.type_key === 'blog' ? 'book-open' : (preset.type_key === 'news' ? 'newspaper' : (preset.type_key === 'portfolio' ? 'briefcase' : (preset.type_key === 'services' ? 'cog' : 'layout')))" class="h-3.5 w-3.5 text-gray-500"></i>
                            <span x-text="preset.label || preset.type_key"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="grid max-h-80 grid-cols-2 gap-3 overflow-y-auto pr-1 sm:grid-cols-3 lg:grid-cols-4">
                <template x-for="bt in blockTypes" :key="bt.id">
                    <button type="button"
                        @click="addBlock(bt.block_key)"
                        class="group relative flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-3 text-center transition-all hover:border-brand-400 hover:bg-brand-50/40">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-500 transition-colors group-hover:bg-brand-100 group-hover:text-brand-600">
                            <i :data-lucide="bt.icon || 'layout-template'" class="h-4.5 w-4.5"></i>
                        </div>
                        <span class="text-[11px] font-semibold leading-tight text-gray-800" x-text="bt.name"></span>
                        <code class="text-[10px] font-mono text-gray-400" x-text="bt.block_key"></code>
                        <span class="text-[10px] font-medium text-brand-600 opacity-0 transition-opacity group-hover:opacity-100">
                            <?= esc(lang('Collections.block_template_builder_add_block')) ?>
                        </span>
                    </button>
                </template>
            </div>

            <div x-show="blockTypes.length === 0" x-cloak class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500">
                <?= esc(lang('Collections.block_template_builder_no_blocks')) ?>
            </div>
        </div>

        <div x-show="activePanel === 'structure'" x-cloak class="mt-4 space-y-4" x-ref="templateList">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h5 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.block_template_builder_template_title')) ?></h5>
                    <p class="mt-1 text-xs text-gray-500">
                        <?= esc(lang('Collections.block_template_builder_template_help')) ?>
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <template x-for="(row, index) in rows" :key="`${row.block_key}-${index}-${row.sort_order}`">
                    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-brand-100 px-2 text-xs font-semibold text-brand-700" x-text="row.sort_order"></span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900" x-text="blockTypeLabel(row.block_key)"></p>
                                    <code class="text-[10px] font-mono text-gray-500" x-text="row.block_key"></code>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button"
                                    @click="row.advancedOpen = !row.advancedOpen"
                                    class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-600 transition-colors hover:border-brand-300 hover:text-brand-700"
                                    :aria-expanded="row.advancedOpen ? 'true' : 'false'">
                                    <span x-text="row.advancedOpen ? '<?= esc(lang('App.hide'), 'js') ?>' : '<?= esc(lang('App.show_more'), 'js') ?>'"></span>
                                </button>
                                <button type="button"
                                    @click="moveBlock(index, -1)"
                                    :disabled="index === 0"
                                    class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40"
                                    :title="row.block_key ? '<?= esc(lang('Collections.block_template_builder_move_up'), 'js') ?>' : ''">
                                    ↑
                                </button>
                                <button type="button"
                                    @click="moveBlock(index, 1)"
                                    :disabled="index === rows.length - 1"
                                    class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40"
                                    :title="row.block_key ? '<?= esc(lang('Collections.block_template_builder_move_down'), 'js') ?>' : ''">
                                    ↓
                                </button>
                                <button type="button"
                                    @click="removeBlock(index)"
                                    class="rounded-lg border border-red-200 bg-white px-2 py-1 text-xs text-red-600 transition-colors hover:border-red-300 hover:bg-red-50">
                                    <?= esc(lang('App.remove')) ?>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-gray-700"><?= esc(lang('Collections.block_template_builder_block_label')) ?></label>
                                <input type="text"
                                    x-model="row.label"
                                    @input="sync()"
                                    class="<?= esc(input_class('block_template')) ?>"
                                    placeholder="<?= esc(lang('Collections.block_template_builder_block_label_placeholder')) ?>">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-gray-700"><?= esc(lang('Collections.block_template_builder_block_key')) ?></label>
                                <select
                                    x-effect="$el.value = row.block_key || ''"
                                    @change="row.block_key = $event.target.value; onBlockKeyChanged(index)"
                                    class="<?= esc(input_class('block_template')) ?>">
                                    <option value=""><?= esc(lang('App.select')) ?></option>
                                    <template x-for="bt in blockTypes" :key="bt.block_key">
                                        <option
                                            :value="bt.block_key"
                                            :selected="row.block_key === bt.block_key"
                                            x-text="`${bt.name} (${bt.block_key})`"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700">
                                <input type="checkbox" x-model="row.required" @change="sync()" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <?= esc(lang('Collections.block_template_builder_required')) ?>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700">
                                <input type="checkbox" x-model="row.locked" @change="sync()" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <?= esc(lang('Collections.block_template_builder_locked')) ?>
                            </label>
                            <span class="text-[11px] text-gray-400" x-show="row.defaults.length > 0" x-text="`${row.defaults.length} <?= esc(lang('Collections.block_template_builder_defaults_title'), 'js') ?>`"></span>
                        </div>

                        <div x-show="row.advancedOpen" x-cloak class="mt-3 rounded-lg border border-gray-200 bg-white p-3">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-gray-700"><?= esc(lang('Collections.block_template_builder_block_help')) ?></label>
                                <input type="text"
                                    x-model="row.help_text"
                                    @input="sync()"
                                    class="<?= esc(input_class('block_template')) ?>"
                                    placeholder="<?= esc(lang('Collections.block_template_builder_block_help_placeholder')) ?>">
                            </div>

                            <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50/70 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <h6 class="text-xs font-semibold text-gray-700"><?= esc(lang('Collections.block_template_builder_defaults_title')) ?></h6>
                                        <p class="mt-0.5 text-[11px] text-gray-500"><?= esc(lang('Collections.block_template_builder_defaults_help')) ?></p>
                                    </div>
                                    <button type="button" @click="addDefault(index)" class="rounded-lg border border-brand-200 bg-brand-50 px-2.5 py-1 text-[11px] font-medium text-brand-700 transition-colors hover:bg-brand-100">
                                        <?= esc(lang('Collections.block_template_builder_add_default')) ?>
                                    </button>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <template x-for="(defaultRow, defaultIndex) in row.defaults" :key="defaultIndex">
                                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-[minmax(0,1fr)_10rem_minmax(0,1fr)_auto]">
                                            <input type="text"
                                                x-model="defaultRow.key"
                                                @input="sync()"
                                                class="<?= esc(input_class('block_template')) ?>"
                                                placeholder="<?= esc(lang('Collections.block_template_builder_default_key_placeholder')) ?>">

                                            <select x-model="defaultRow.type" @change="sync()" class="<?= esc(input_class('block_template')) ?>">
                                                <option value="string"><?= esc(lang('Collections.block_template_builder_type_string')) ?></option>
                                                <option value="number"><?= esc(lang('Collections.block_template_builder_type_number')) ?></option>
                                                <option value="boolean"><?= esc(lang('Collections.block_template_builder_type_boolean')) ?></option>
                                            </select>

                                            <template x-if="defaultRow.type === 'boolean'">
                                                <select x-model="defaultRow.value" @change="sync()" class="<?= esc(input_class('block_template')) ?>">
                                                    <option value="1"><?= esc(lang('App.yes')) ?></option>
                                                    <option value="0"><?= esc(lang('App.no')) ?></option>
                                                </select>
                                            </template>

                                            <template x-if="defaultRow.type === 'number'">
                                                <input type="number"
                                                    x-model="defaultRow.value"
                                                    @input="sync()"
                                                    class="<?= esc(input_class('block_template')) ?>"
                                                    placeholder="0">
                                            </template>

                                            <template x-if="defaultRow.type === 'string'">
                                                <input type="text"
                                                    x-model="defaultRow.value"
                                                    @input="sync()"
                                                    class="<?= esc(input_class('block_template')) ?>"
                                                    placeholder="<?= esc(lang('Collections.block_template_builder_default_value_placeholder')) ?>">
                                            </template>

                                            <button type="button"
                                                @click="removeDefault(index, defaultIndex)"
                                                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-red-600 transition-colors hover:border-red-300 hover:bg-red-50">
                                                <?= esc(lang('App.remove')) ?>
                                            </button>
                                        </div>
                                    </template>

                                    <p x-show="row.defaults.length === 0" x-cloak class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-400">
                                        <?= esc(lang('Collections.block_template_builder_defaults_empty')) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </template>

                <p x-show="rows.length === 0" x-cloak class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                    <?= esc(lang('Collections.block_template_builder_empty')) ?>
                </p>
            </div>
        </div>

        <!-- ── WIZARD STEPS PANEL ── -->
        <div x-show="activePanel === 'wizard-steps'" x-cloak class="mt-4 space-y-4" x-ref="wizardStepsList">
            <div>
                <h5 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.wizard_steps_builder_title')) ?></h5>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Collections.wizard_steps_builder_help')) ?></p>
            </div>

            <p x-show="wizardTitleMissing()" x-cloak
               class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                <?= esc(lang('Collections.wizard_steps_builder_title_missing_warning')) ?>
            </p>

            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                <h6 class="text-xs font-semibold text-gray-900 mb-1"><?= esc(lang('Collections.wizard_steps_builder_catalog_title')) ?></h6>
                <p class="text-xs text-gray-500 mb-3"><?= esc(lang('Collections.wizard_steps_builder_catalog_help')) ?></p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="fieldKey in availableWizardFields()" :key="fieldKey">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm"
                              x-text="wizardFieldLabel(fieldKey)"></span>
                    </template>
                </div>
                <p x-show="availableWizardFields().length === 0" x-cloak class="text-xs text-gray-400 italic mt-2">
                    <?= esc(lang('Collections.wizard_steps_builder_catalog_empty')) ?>
                </p>
            </div>

            <div class="space-y-4">
                <template x-for="(step, stepIndex) in stepRows" :key="stepIndex">
                    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-brand-100 px-2 text-xs font-semibold text-brand-700" x-text="stepIndex + 1"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="moveStep(stepIndex, -1)" :disabled="stepIndex === 0"
                                        class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40">↑</button>
                                <button type="button" @click="moveStep(stepIndex, 1)" :disabled="stepIndex === stepRows.length - 1"
                                        class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs text-gray-500 transition-colors hover:border-brand-300 hover:text-brand-700 disabled:cursor-not-allowed disabled:opacity-40">↓</button>
                                <button type="button" @click="removeStep(stepIndex)"
                                        class="rounded-lg border border-red-200 bg-white px-2 py-1 text-xs text-red-600 transition-colors hover:border-red-300 hover:bg-red-50">
                                    <?= esc(lang('Collections.wizard_steps_builder_remove_step')) ?>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-3 lg:grid-cols-2">
                            <input type="text" x-model="step.step_title" @input="sync()"
                                   placeholder="<?= esc(lang('Collections.wizard_steps_builder_step_title_placeholder'), 'attr') ?>"
                                   class="<?= esc(input_class('wizard_config')) ?>">
                            <input type="text" x-model="step.step_hint" @input="sync()"
                                   placeholder="<?= esc(lang('Collections.wizard_steps_builder_step_hint_placeholder'), 'attr') ?>"
                                   class="<?= esc(input_class('wizard_config')) ?>">
                        </div>

                        <div class="mt-3 space-y-2">
                            <template x-for="(field, fieldIndex) in step.fields" :key="field.key">
                                <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                                    <span class="text-xs font-mono text-gray-400" x-text="field.key"></span>
                                    <input type="text" x-model="field.label" @input="sync()"
                                           class="flex-1 min-w-[8rem] rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-900">
                                    <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600">
                                        <input type="checkbox" x-model="field.required" @change="sync()" class="h-3.5 w-3.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                        <?= esc(lang('Collections.wizard_steps_builder_required')) ?>
                                    </label>
                                    <button type="button" @click="removeFieldFromStep(stepIndex, fieldIndex)"
                                            class="text-xs font-medium text-red-600 hover:text-red-700">
                                        <?= esc(lang('Collections.wizard_steps_builder_remove_field')) ?>
                                    </button>
                                </div>
                            </template>
                            <p x-show="step.fields.length === 0" x-cloak class="text-xs text-gray-400 italic">
                                <?= esc(lang('Collections.wizard_steps_builder_empty_fields')) ?>
                            </p>
                        </div>

                        <div class="mt-3" x-show="availableWizardFields().length > 0" x-cloak>
                            <select @change="addFieldToStep(stepIndex, $event.target.value); $event.target.value = ''"
                                    class="<?= esc(input_class('wizard_config')) ?> text-xs">
                                <option value=""><?= esc(lang('Collections.wizard_steps_builder_add_field_placeholder')) ?></option>
                                <template x-for="fieldKey in availableWizardFields()" :key="fieldKey">
                                    <option :value="fieldKey" x-text="wizardFieldLabel(fieldKey)"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>

                <p x-show="stepRows.length === 0" x-cloak class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                    <?= esc(lang('Collections.wizard_steps_builder_empty_steps')) ?>
                </p>
            </div>

            <button type="button" @click="addStep()"
                    class="rounded-lg border border-dashed border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-600 transition-colors hover:border-brand-400 hover:text-brand-700">
                <?= esc(lang('Collections.wizard_steps_builder_add_step')) ?>
            </button>
        </div>
    </section>

    <details class="rounded-xl border border-gray-200 bg-white p-4">
        <summary class="cursor-pointer select-none text-sm font-medium text-gray-700">
            <?= esc(lang('Collections.block_template_builder_preview_json')) ?>
        </summary>
        <pre class="mt-3 max-h-80 overflow-auto rounded-lg bg-gray-950 p-4 text-xs leading-relaxed text-gray-100" x-text="json"></pre>
    </details>

    <details class="rounded-xl border border-gray-200 bg-white p-4">
        <summary class="cursor-pointer select-none text-sm font-medium text-gray-700">
            <?= esc(lang('Collections.wizard_steps_builder_preview_json')) ?>
        </summary>
        <pre class="mt-3 max-h-80 overflow-auto rounded-lg bg-gray-950 p-4 text-xs leading-relaxed text-gray-100" x-text="wizardConfigJson"></pre>
    </details>

    <?php if (! empty($errors['block_template'])): ?>
        <p class="text-xs text-red-600"><?= esc($errors['block_template']) ?></p>
    <?php endif; ?>
</div>
