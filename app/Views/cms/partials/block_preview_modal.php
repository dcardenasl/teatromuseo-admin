<div x-data="blockPreview()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     @keydown.escape.window="close()"
     @block-preview-open.window="openWithEvent($event)">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-[96vw] h-[92vh] flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Vista Previa del Bloque</h3>
                <p x-show="blockKey" class="text-xs text-gray-500 mt-0.5">
                    Diseño: <code x-text="blockKey" class="font-mono bg-gray-100 px-1 rounded"></code>
                </p>
            </div>
            <button @click="close()" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="flex-1 p-6 bg-slate-50/50 flex flex-col min-h-0 overflow-hidden">

            <!-- Loading -->
            <div x-show="loading" class="flex items-center justify-center py-16 shrink-0">
                <div class="w-8 h-8 border-4 border-brand-600 border-t-transparent rounded-full animate-spin"></div>
                <span class="ml-3 text-sm text-gray-500">Generando preview…</span>
            </div>

            <!-- Error -->
            <div x-show="!loading && error" class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700 shrink-0">
                <p x-text="error"></p>
            </div>

            <!-- Device Selector -->
            <div x-show="!loading && !error && html" class="flex items-center justify-center gap-2 mb-4 p-2 bg-gray-50 border border-gray-200 rounded-xl shrink-0">
                <button @click="deviceMode = 'desktop'" :class="deviceMode === 'desktop' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    Escritorio
                </button>
                <button @click="deviceMode = 'tablet'" :class="deviceMode === 'tablet' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tablet"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><line x1="12" x2="12" y1="18" y2="18"/></svg>
                    Tablet (768px)
                </button>
                <button @click="deviceMode = 'mobile'" :class="deviceMode === 'mobile' ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smartphone"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><line x1="12" x2="12" y1="18" y2="18"/></svg>
                    Móvil (375px)
                </button>
            </div>

            <!-- Preview HTML Container -->
            <meta name="public-site-url" content="<?= esc(rtrim((string) env('PUBLIC_SITE_URL'), '/')) ?>">
            <div x-show="!loading && !error && html" class="flex-1 flex justify-center bg-gray-100 p-4 border border-gray-200 rounded-xl min-h-0 overflow-hidden">
                <div :class="{
                        'w-full': deviceMode === 'desktop',
                        'w-[768px]': deviceMode === 'tablet',
                        'w-[375px]': deviceMode === 'mobile'
                     }"
                     class="transition-all duration-300 ease-in-out border border-gray-200 bg-white shadow-md rounded-lg flex flex-col min-h-0 overflow-hidden">
                    <iframe x-ref="previewIframe" class="w-full h-full border-0 bg-transparent flex-1"></iframe>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 shrink-0 flex justify-end">
            <button @click="close()" class="btn-secondary text-sm">Cerrar</button>
        </div>
    </div>
</div>
