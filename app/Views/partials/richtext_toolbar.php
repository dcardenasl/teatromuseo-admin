<?php
/**
 * Shared toolbar markup for the Tiptap rich text editor.
 *
 * Requires the surrounding Alpine component to expose `isActive(type, attrs?)`.
 */
?>
<div class="flex flex-wrap items-center gap-0.5 border-b border-gray-200 bg-gray-50 px-2 py-1.5"
     data-richtext-toolbar
     @mousedown.prevent>
    <button type="button"
            data-richtext-action="bold"
            :class="isActive('bold') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded text-sm font-bold transition-colors"
            title="Negrita (Ctrl+B)"
            aria-label="Negrita">B</button>

    <button type="button"
            data-richtext-action="italic"
            :class="isActive('italic') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded text-sm italic transition-colors"
            title="Cursiva (Ctrl+I)"
            aria-label="Cursiva">I</button>

    <button type="button"
            data-richtext-action="strike"
            :class="isActive('strike') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded text-sm line-through transition-colors"
            title="Tachado"
            aria-label="Tachado">S</button>

    <button type="button"
            data-richtext-action="code"
            :class="isActive('code') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded text-xs font-mono transition-colors"
            title="Código inline"
            aria-label="Código inline">&lt;/&gt;</button>

    <span class="w-px h-5 bg-gray-300 mx-1"></span>

    <button type="button"
            data-richtext-action="heading"
            data-richtext-level="2"
            :class="isActive('heading', { level: 2 }) ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="px-2 h-7 flex items-center justify-center rounded text-xs font-bold transition-colors"
            title="Título H2"
            aria-label="Título H2">H2</button>

    <button type="button"
            data-richtext-action="heading"
            data-richtext-level="3"
            :class="isActive('heading', { level: 3 }) ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="px-2 h-7 flex items-center justify-center rounded text-xs font-bold transition-colors"
            title="Título H3"
            aria-label="Título H3">H3</button>

    <span class="w-px h-5 bg-gray-300 mx-1"></span>

    <button type="button"
            data-richtext-action="bulletList"
            :class="isActive('bulletList') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded transition-colors"
            title="Lista sin orden"
            aria-label="Lista sin orden">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
    </button>

    <button type="button"
            data-richtext-action="orderedList"
            :class="isActive('orderedList') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded transition-colors"
            title="Lista numerada"
            aria-label="Lista numerada">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.242 5.992h12m-12 6.003H20.24m-12 5.999h12M4.117 7.495v-3.75H2.99m1.125 3.75H2.99m1.125 0H5.24m-1.92 2.577a1.125 1.125 0 1 1 1.591 1.59l-1.83 1.83h2.16M2.99 15.745h1.125a1.125 1.125 0 0 1 0 2.25H3.74m0-.002v.002m0 0H2.99" />
        </svg>
    </button>

    <span class="w-px h-5 bg-gray-300 mx-1"></span>

    <button type="button"
            data-richtext-action="blockquote"
            :class="isActive('blockquote') ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded transition-colors"
            title="Cita"
            aria-label="Cita">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
        </svg>
    </button>

    <button type="button"
            data-richtext-action="link"
            :class="isActive('link') ? 'bg-gray-200 text-brand-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'"
            class="w-7 h-7 flex items-center justify-center rounded transition-colors"
            title="Enlace"
            aria-label="Enlace">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
        </svg>
    </button>

    <button type="button"
            data-richtext-action="hr"
            class="w-7 h-7 flex items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-800 transition-colors"
            title="Separador horizontal"
            aria-label="Separador horizontal">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
        </svg>
    </button>

    <span class="flex-1"></span>

    <button type="button"
            data-richtext-action="undo"
            class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            title="Deshacer (Ctrl+Z)"
            aria-label="Deshacer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
        </svg>
    </button>

    <button type="button"
            data-richtext-action="redo"
            class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            title="Rehacer (Ctrl+Y)"
            aria-label="Rehacer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3"/>
        </svg>
    </button>
</div>
