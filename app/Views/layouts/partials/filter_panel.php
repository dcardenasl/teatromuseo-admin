<?php
$actionUrl ??= current_url();
$clearUrl ??= $actionUrl;
$method ??= 'get';
$title ??= lang('App.filters');
$submitLabel ??= lang('App.search');
$submitFullWidth ??= false;
$hasFilters ??= has_active_filters();
$fieldsView ??= null;
$fieldsData ??= [];
$reactiveHasFilters ??= false;
$filterDefaults ??= [];
$ignoredFilterKeys ??= ['sort', 'page', 'cursor'];

$normalizedDefaults = [];
if (is_array($filterDefaults)) {
    foreach ($filterDefaults as $key => $value) {
        if (! is_string($key) || $key === '') {
            continue;
        }
        if (! is_scalar($value) && $value !== null) {
            continue;
        }

        $normalizedDefaults[$key] = trim((string) $value);
    }
}

$normalizedIgnoredKeys = [];
if (is_array($ignoredFilterKeys)) {
    foreach ($ignoredFilterKeys as $key) {
        if (is_string($key) && $key !== '') {
            $normalizedIgnoredKeys[] = $key;
        }
    }
}

$defaultsJson = json_encode($normalizedDefaults);
if (! is_string($defaultsJson) || $defaultsJson === '') {
    $defaultsJson = '{}';
}

$ignoredJson = json_encode($normalizedIgnoredKeys);
if (! is_string($ignoredJson) || $ignoredJson === '') {
    $ignoredJson = '[]';
}

?>
<form
    method="<?= esc($method) ?>"
    action="<?= esc($actionUrl) ?>"
    class="<?= esc(filter_panel_class()) ?>"
    data-table-filter-form="1"
    data-reactive-has-filters="<?= $reactiveHasFilters ? '1' : '0' ?>"
    data-filter-defaults="<?= esc($defaultsJson) ?>"
    data-filter-ignored="<?= esc($ignoredJson) ?>"
>
    <div class="flex items-center justify-between gap-3">
        <h4 class="text-sm font-semibold text-gray-800"><?= esc($title) ?></h4>
        <?php if ($reactiveHasFilters || $hasFilters): ?>
            <a
                href="<?= esc($clearUrl) ?>"
                class="text-xs font-medium text-brand-700 hover:text-brand-800 hover:underline"
                <?php if ($reactiveHasFilters): ?>
                    x-cloak
                    x-show="hasActiveFilters()"
                <?php endif; ?>
            ><?= lang('App.clear_filters') ?></a>
        <?php endif; ?>
    </div>

    <?php if ($reactiveHasFilters || $hasFilters): ?>
        <p
            class="mt-2 text-xs text-gray-500"
            <?php if ($reactiveHasFilters): ?>
                x-cloak
                x-show="hasActiveFilters()"
            <?php endif; ?>
        >
            <?= esc(lang('App.filters_active')) ?>
        </p>
        <p
            class="mt-2 text-xs text-gray-500"
            <?php if ($reactiveHasFilters): ?>
                x-cloak
                x-show="!hasActiveFilters()"
            <?php endif; ?>
            >
            <?= esc(lang('App.no_filters_active')) ?>
        </p>
        <div x-show="hasActiveFilters()" class="mt-3 flex flex-wrap gap-2" x-cloak>
            <template x-for="key in Object.keys(query).filter(k => !ignoredFilterKeys.has(k) && query[k] !== '' && query[k] !== filterDefaults[k])" :key="key">
                <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 border border-brand-200 px-2.5 py-1 text-xs font-semibold text-brand-700">
                    <span x-text="`${key}: ${query[key]}`" class="capitalize"></span>
                    <button type="button" @click="query[key] = filterDefaults[key] || ''; applyQueryToForm(); fetchData(true);" class="text-brand-500 hover:text-brand-700 focus:outline-none">
                        <?= ui_icon('x', 'h-3 w-3') ?>
                    </button>
                </span>
            </template>
        </div>
    <?php endif; ?>

    <p
        class="mt-2 text-xs text-gray-500"
        x-cloak
        x-show="loading"
    >
        <?= esc(lang('App.loading_refreshing')) ?>
    </p>

    <?php if (is_string($fieldsView) && $fieldsView !== ''): ?>
        <?= view($fieldsView, is_array($fieldsData) ? $fieldsData : []) ?>
    <?php endif; ?>

    <div class="mt-3 flex items-center justify-end gap-2">
        <button type="submit" class="<?= esc(filter_submit_button_class((bool) $submitFullWidth)) ?>">
            <?= ui_icon('search', 'h-3.5 w-3.5') ?>
            <?= esc($submitLabel) ?>
        </button>
    </div>
</form>
