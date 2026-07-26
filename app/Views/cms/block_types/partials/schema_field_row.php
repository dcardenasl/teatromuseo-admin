<?php
/**
 * Schema field row partial — used in both create and edit BlockType forms.
 * Requires Alpine.js context with `schemaFields` / `configFields` arrays.
 * @var string $section  'content' | 'config'
 */
$isConfig = ($section ?? 'content') === 'config';
$arrayVar = $isConfig ? 'configFields' : 'schemaFields';
$sectionKey = $isConfig ? 'config' : 'content';
?>
<div class="flex flex-col p-3 border border-gray-200 rounded-lg bg-gray-50 hover:bg-white transition-colors w-full">
    <div class="flex items-start gap-2 w-full">
        <!-- Key -->
        <div class="flex-1 min-w-0">
            <label class="block text-[10px] font-medium text-gray-500 mb-1">Clave</label>
            <input type="text"
                   x-model="field.key"
                   @input="rebuildJson()"
                   placeholder="ej: heading"
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs font-mono text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
        </div>

        <!-- Tipo -->
        <div class="w-28 shrink-0">
            <label class="block text-[10px] font-medium text-gray-500 mb-1">Tipo</label>
            <select x-model="field.type"
                    @change="rebuildJson()"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="string">string</option>
                <option value="text">text</option>
                <option value="richtext">richtext</option>
                <option value="url">url</option>
                <option value="integer">integer</option>
                <option value="boolean">boolean</option>
                <option value="select">select</option>
                <option value="color">color</option>
                <option value="media_reference">media_reference</option>
                <?php if (!$isConfig): ?>
                    <option value="repeater">repeater</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Label -->
        <div class="flex-1 min-w-0">
            <label class="block text-[10px] font-medium text-gray-500 mb-1">Etiqueta</label>
            <input type="text"
                   x-model="field.label"
                   @input="rebuildJson()"
                   placeholder="ej: Título"
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
        </div>

        <!-- Opciones (solo select) -->
        <div class="flex-1 min-w-0" x-show="field.type === 'select'">
            <label class="block text-[10px] font-medium text-gray-500 mb-1">Opciones (coma)</label>
            <input type="text"
                   x-model="field.options"
                   @input="rebuildJson()"
                   placeholder="opción1, opción2"
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
        </div>

        <!-- Tipo de archivo aceptado (solo media_reference) -->
        <div class="w-28 shrink-0" x-show="field.type === 'media_reference'" x-cloak>
            <label class="block text-[10px] font-medium text-gray-500 mb-1">Acepta</label>
            <select x-model="field.accept"
                    @change="rebuildJson()"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="image">image</option>
                <option value="document">document</option>
                <option value="video">video</option>
                <option value="audio">audio</option>
                <option value="any">any</option>
            </select>
        </div>

        <!-- Requerido -->
        <div class="pt-4 shrink-0">
            <label class="flex items-center gap-1 text-[10px] text-gray-500 cursor-pointer">
                <input type="checkbox" x-model="field.required" @change="rebuildJson()" class="rounded border-gray-300 text-brand-600">
                Req.
            </label>
        </div>

        <!-- Eliminar -->
        <button type="button"
                @click="removeField('<?= $sectionKey ?>', index)"
                class="pt-4 shrink-0 text-gray-400 hover:text-red-500 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
        </button>
    </div>

    <!-- Subcampos para Repeater -->
    <div x-show="field.type === 'repeater'" class="mt-3 ml-6 pl-4 border-l-2 border-brand-200 space-y-2" x-cloak>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-700">Subcampos del Repetidor</span>
            <button type="button" @click="addField('<?= $sectionKey ?>', field)" class="text-[10px] bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 rounded px-2 py-0.5">
                + Añadir subcampo
            </button>
        </div>
        <div class="space-y-2">
            <template x-for="(subField, subIndex) in field.item_fields" :key="subIndex">
                <div class="flex items-start gap-2 p-2 border border-gray-200 rounded bg-white">
                    <!-- Key -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Clave</label>
                        <input type="text" x-model="subField.key" @input="rebuildJson()" placeholder="ej: sub_title" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-[11px] font-mono text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    </div>
                    <!-- Tipo -->
                    <div class="w-24 shrink-0">
                        <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Tipo</label>
                        <select x-model="subField.type" @change="rebuildJson()" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-[11px] text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="string">string</option>
                            <option value="text">text</option>
                            <option value="url">url</option>
                            <option value="media_reference">media_reference</option>
                        </select>
                    </div>
                    <!-- Acepta (solo media_reference) -->
                    <div class="w-20 shrink-0" x-show="subField.type === 'media_reference'" x-cloak>
                        <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Acepta</label>
                        <select x-model="subField.accept" @change="rebuildJson()" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-[11px] text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="image">image</option>
                            <option value="document">document</option>
                            <option value="video">video</option>
                            <option value="audio">audio</option>
                            <option value="any">any</option>
                        </select>
                    </div>
                    <!-- Label -->
                    <div class="flex-1 min-w-0">
                        <label class="block text-[9px] font-medium text-gray-400 mb-0.5">Etiqueta</label>
                        <input type="text" x-model="subField.label" @input="rebuildJson()" placeholder="ej: Subtítulo" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-[11px] text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    </div>
                    <!-- Requerido -->
                    <div class="pt-3 shrink-0">
                        <label class="flex items-center gap-0.5 text-[9px] text-gray-400 cursor-pointer">
                            <input type="checkbox" x-model="subField.required" @change="rebuildJson()" class="rounded border-gray-300 text-brand-600 scale-75">
                            Req.
                        </label>
                    </div>
                    <!-- Eliminar -->
                    <button type="button" @click="removeField('<?= $sectionKey ?>', subIndex, field)" class="pt-3 shrink-0 text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                        </svg>
                    </button>
                </div>
            </template>
            <p x-show="!field.item_fields || field.item_fields.length === 0" class="text-[11px] text-gray-400 italic text-center py-1.5 border border-dashed border-gray-200 rounded">
                Sin subcampos. Añade al menos uno para estructurar el repetidor.
            </p>
        </div>
    </div>
</div>
