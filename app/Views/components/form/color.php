<?php
/**
 * Color Picker Component
 *
 * Renders an interactive color picker with preset palette and custom input.
 * Uses Alpine.js for interactivity. Follows the same $name/$value/$label
 * contract as the other components/form/* partials (see text.php, select.php).
 *
 * @var string $name
 * @var string|null $label
 * @var mixed|null $value
 * @var bool|null $required
 * @var bool|null $show_error
 * @var string|null $placeholder
 */

helper('form');

$required   = $required ?? false;
$label      = $label ?? '';
$show_error = $show_error ?? true;
$value      = old($name, $value ?? '');
$placeholder = $placeholder ?? '#ffffff o rgb(...)';
$presets = [
    ['hex' => '', 'name' => 'Transparente'],
    ['hex' => '#ffffff', 'name' => 'Blanco'],
    ['hex' => '#000000', 'name' => 'Negro'],
    ['hex' => '#3b82f6', 'name' => 'Azul'],
    ['hex' => '#10b981', 'name' => 'Verde'],
    ['hex' => '#ef4444', 'name' => 'Rojo'],
    ['hex' => '#f59e0b', 'name' => 'Naranja'],
    ['hex' => '#8b5cf6', 'name' => 'Violeta'],
    ['hex' => '#6b7280', 'name' => 'Gris'],
    ['hex' => '#f3f4f6', 'name' => 'Gris Claro'],
    ['hex' => '#1e3a8a', 'name' => 'Azul Oscuro'],
    ['hex' => '#065f46', 'name' => 'Verde Oscuro'],
    ['hex' => '#991b1b', 'name' => 'Rojo Oscuro'],
];
?>

<?php if ($label !== ''): ?>
    <label class="block text-sm font-medium text-gray-700" for="<?= esc($name, 'attr') ?>">
        <?= lang($label) ?>
        <?php if ($required): ?>
            <span class="text-red-500" aria-hidden="true">*</span>
        <?php endif; ?>
    </label>
<?php endif; ?>
<div x-data="{
    value: '<?= esc((string) $value, 'attr') ?>',
    open: false,
    presets: <?= esc(json_encode($presets, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG), 'attr') ?>
}"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    @color-picker-toggle.window="if ($event.detail !== $el) open = false"
    x-effect="if (open) $dispatch('color-picker-toggle', $el)"
    class="relative">
    <div class="mt-1 flex items-center gap-2">
        <!-- Color swatch button -->
        <button
            type="button"
            @click="open = !open"
            class="h-10 w-10 shrink-0 rounded-lg border border-gray-300 shadow-sm transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-500"
            :style="value ? `background-color: ${value}` : 'background-image: linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%); background-size: 8px 8px; background-position: 0 0, 0 4px, 4px -4px, -4px 0px; background-color: #fff;'"
            aria-label="Open color picker"
        ></button>

        <!-- Text input -->
        <div class="flex-1 relative">
            <input
                id="<?= esc($name, 'attr') ?>"
                type="text"
                name="<?= esc($name, 'attr') ?>"
                x-model="value"
                placeholder="<?= esc($placeholder, 'attr') ?>"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-mono text-gray-900 uppercase shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 pl-3 pr-10"
                <?= $required ? 'required' : '' ?>
                <?= field_aria_attrs($name, $required) ?>
            >
            <!-- Dropdown toggle -->
            <button
                type="button"
                @click="open = !open"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                aria-label="Toggle color preset palette"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Color picker dropdown -->
    <div
        x-show="open"
        class="absolute left-0 z-50 mt-2 p-3 bg-white border border-gray-200 rounded-xl shadow-xl w-64 max-w-sm"
        x-cloak
    >
        <div class="flex items-center justify-between mb-2">
            <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-400">Paleta Predefinida</span>
            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600" aria-label="Cerrar selector de color">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Preset grid -->
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

        <!-- Custom color picker -->
        <div class="border-t border-gray-100 pt-3 flex items-center justify-between gap-2">
            <span class="text-xs text-gray-500">Personalizado:</span>
            <input
                type="color"
                x-model="value"
                class="h-8 w-8 cursor-pointer rounded border border-gray-200 p-0 bg-transparent"
                aria-label="Custom color picker"
            >
        </div>
    </div>
</div>
<?php if ($show_error): ?>
    <?= render_field_error($name) ?>
<?php endif; ?>
