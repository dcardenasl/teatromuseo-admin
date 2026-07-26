import { bootLucideIcons } from './utils/lucide.js';
import { bootSlugFields } from './utils/slug.js';
import { bestFilePreviewUrl, resolveTranslatableFilePreviewUrl } from './utils/fileUrl.js';
import { formValuesToObject } from './utils/formSerialization.js';
import { buildConfirmDeleteMessage } from './utils/labels.js';
import { resolveCmsTranslationEditUrl } from './utils/translationNavigation.js';
import { copyDefaultToAll } from './utils/translationCopy.js';

import { confirmStore } from './stores/confirm.store.js';
import { toastStore } from './stores/toast.store.js';
import { filePickerStore } from './stores/filePicker.store.js';

import { appShell } from './components/appShell.js';
import { remoteTableFactory } from './components/remoteTable.js';
import { formFieldBuilderFactory } from './components/formFieldBuilder.js';
import { filePickerField } from './components/filePickerField.js';
import { translatableFileField } from './components/translatableFileField.js';
import { mediaReferenceField } from './components/mediaReferenceField.js';
import { blockRepeaterField } from './components/blockRepeaterField.js';
import { adminMetadataField } from './components/adminMetadataField.js';
import { adminMediaGallery } from './components/adminMediaGallery.js';
import { langTabs } from './components/langTabs.js';
import { jsonEditor } from './components/jsonEditor.js';
import { blockPreview } from './components/blockPreview.js';
import { blockTypeDesigner } from './components/blockTypeDesigner.js';
import { blockInstanceConfig } from './components/blockInstanceConfig.js';
import { schemaEditor } from './components/schemaEditor.js';
import { blockSorter } from './components/blockSorter.js';
import { bootSessionExpiryWatcher } from './components/sessionWatcher.js';
import { handleGoogleCredentialResponse } from './components/googleAuth.js';
import { richTextEditor } from './components/richTextEditor.js';
import {
    copyLangTabsFileFieldToAll,
    copyLangTabsFileFieldToTargets,
    copyLangTabsMediaReferenceFieldToAll,
} from './components/langTabs.js';
import { wizard } from './components/wizard/index.js';
import { structureWizard } from './components/wizard/structureIndex.js';

document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', confirmStore());
    Alpine.store('toast', toastStore);
    Alpine.store('filePicker', filePickerStore);

    Alpine.data('appShell', appShell);
    Alpine.data('remoteTable', remoteTableFactory);
    Alpine.data('formFieldBuilder', formFieldBuilderFactory);
    Alpine.data('filePickerField', filePickerField);
    Alpine.data('translatableFileField', translatableFileField);
    Alpine.data('mediaReferenceField', mediaReferenceField);
    Alpine.data('blockRepeaterField', blockRepeaterField);
    Alpine.data('adminMetadataField', adminMetadataField);
    Alpine.data('adminMediaGallery', adminMediaGallery);
    Alpine.data('langTabs', langTabs);
    Alpine.data('jsonEditor', jsonEditor);
    Alpine.data('blockPreview', blockPreview);
    Alpine.data('blockTypeDesigner', blockTypeDesigner);
    Alpine.data('blockInstanceConfig', blockInstanceConfig);
    Alpine.data('schemaEditor', schemaEditor);
    Alpine.data('blockSorter', blockSorter);
    Alpine.data('wizard', wizard);
    Alpine.data('structureWizard', structureWizard);

    // Window globals expected by PHP views and other components
    window.remoteTable = remoteTableFactory;
    window.formFieldBuilder = formFieldBuilderFactory;
    window.confirmDeleteMessage = buildConfirmDeleteMessage;
    window.bestFilePreviewUrl = bestFilePreviewUrl;
    window.resolveTranslatableFilePreviewUrl = resolveTranslatableFilePreviewUrl;
    window.resolveCmsTranslationEditUrl = resolveCmsTranslationEditUrl;
    window.copyDefaultToAll = copyDefaultToAll;
    window.formValuesToObject = formValuesToObject;
    window.copyLangTabsFileFieldToTargets = copyLangTabsFileFieldToTargets;
    window.copyLangTabsFileFieldToAll = copyLangTabsFileFieldToAll;
    window.copyLangTabsMediaReferenceFieldToAll = copyLangTabsMediaReferenceFieldToAll;
    window.blockInstanceConfigFactory = blockInstanceConfig;
});

// Must be on window before the Google GSI script fires
window.handleGoogleCredentialResponse = handleGoogleCredentialResponse;
window.richTextEditor = richTextEditor;

let lucideBootstrapped = false;
let globalSubmitState = null;
let lastActionButtonClick = null;

document.addEventListener('DOMContentLoaded', () => {
    if (!lucideBootstrapped) { bootLucideIcons(); lucideBootstrapped = true; }
    bootSlugFields();
    bootGlobalSubmitGuard();
    const config = window.__componentConfig || {};
    bootSessionExpiryWatcher({ expiringMessage: config.sessionExpiringMessage });
});

window.addEventListener('load', () => {
    if (!lucideBootstrapped) { bootLucideIcons(); lucideBootstrapped = true; }
});

function bootGlobalSubmitGuard() {
    if (window.__adminGlobalSubmitGuard) return;
    window.__adminGlobalSubmitGuard = true;
    bootGlobalSubmitFetchBridge();
    document.addEventListener('click', (event) => {
        const actionButton = event.target instanceof HTMLElement
            ? event.target.closest('button, input[type="button"], input[type="submit"], input[type="reset"]')
            : null;
        if (actionButton instanceof HTMLElement) {
            lastActionButtonClick = { button: actionButton, at: window.performance.now() };
        }
        const submitter = actionButton instanceof HTMLElement
            && actionButton.matches('button[type="submit"], button:not([type]), input[type="submit"]')
            ? actionButton
            : null;
        if (!(submitter instanceof HTMLElement)) return;
        const form = submitter.form;
        if (!(form instanceof HTMLFormElement) || form.dataset.globalSubmitting === '1') return;
        const method = (form.getAttribute('method') || 'get').toUpperCase();
        const alpineSubmit = form.getAttribute('@submit.prevent') || form.getAttribute('x-on:submit.prevent') || '';
        // Confirmation forms must not show the blocking state before the user
        // accepts the destructive action. The confirmation callback explicitly
        // starts the guard after consent is given.
        if (alpineSubmit.includes('$store.confirm')) return;
        if (!isMutatingForm(method, alpineSubmit)) return;
        if (!form.checkValidity()) return;
        form.dataset.globalSubmitting = '1';
        startGlobalSubmitGuard(submitter, true, isAsyncForm(alpineSubmit), form);
    }, true);
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.noSubmitGuard === '1') return;
        if (form.dataset.globalSubmitting === '1') return;
        const method = (form.getAttribute('method') || 'get').toUpperCase();
        const alpineSubmit = form.getAttribute('@submit.prevent') || form.getAttribute('x-on:submit.prevent') || '';
        if (alpineSubmit.includes('$store.confirm') || !isMutatingForm(method, alpineSubmit)) return;
        form.dataset.globalSubmitting = '1';
        startGlobalSubmitGuard(event.submitter, false, isAsyncForm(alpineSubmit), form);
    }, true);
    window.adminBeginSubmitGuard = startGlobalSubmitGuard;
    window.adminEndSubmitGuard = finishGlobalSubmitGuard;
}

function isMutatingForm(method, alpineSubmit) {
    return ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) || alpineSubmit !== '';
}

function isMutationMethod(method) {
    return ['POST', 'PUT', 'PATCH', 'DELETE'].includes(String(method).toUpperCase());
}

function isAsyncForm(alpineSubmit) {
    return alpineSubmit !== '';
}

function startGlobalSubmitGuard(submitter = null, deferDisable = false, asyncCompletion = false, form = null) {
    if (globalSubmitState) return;

    globalSubmitState = {
        asyncCompletion,
        pendingRequests: 0,
        finishTimerId: null,
        disabledControls: [],
        bodyWasLocked: document.body.classList.contains('overflow-hidden'),
        form: form instanceof HTMLFormElement
            ? form
            : (submitter instanceof HTMLElement ? submitter.form : null),
        timeoutId: window.setTimeout(finishGlobalSubmitGuard, 120000),
    };
    document.body.classList.add('overflow-hidden');
    if (submitter instanceof HTMLElement) {
        globalSubmitState.submitter = submitter;
        submitter.dataset.submitOriginalHtml ??= submitter.innerHTML;
        submitter.innerHTML = '<span style="display:inline-flex;align-items:center;gap:.5rem"><span style="width:.85rem;height:.85rem;border:2px solid currentColor;border-right-color:transparent;border-radius:9999px;animation:admin-submit-spin .8s linear infinite"></span>Guardando…</span>';
        submitter.setAttribute('aria-busy', 'true');
    }
    const disableControls = () => document.querySelectorAll('button, a, input[type="button"], input[type="submit"], input[type="reset"]').forEach((control) => {
        if (control.closest('#global-submit-overlay')) return;
        control.setAttribute('aria-disabled', 'true');
        if ('disabled' in control && !control.disabled) {
            control.disabled = true;
            globalSubmitState?.disabledControls.push(control);
        }
    });
    if (deferDisable) window.setTimeout(disableControls, 0);
    else disableControls();
    const overlay = getGlobalSubmitOverlay();
    overlay.hidden = false;
    overlay.setAttribute('aria-hidden', 'false');
}

function finishGlobalSubmitGuard() {
    const state = globalSubmitState;
    if (!state) return;
    globalSubmitState = null;
    window.clearTimeout(state.timeoutId);
    if (state.finishTimerId !== null) window.clearTimeout(state.finishTimerId);
    state.form?.removeAttribute('data-global-submitting');
    state.disabledControls.forEach((control) => {
        control.disabled = false;
        control.removeAttribute('aria-disabled');
    });
    if (state.submitter instanceof HTMLElement) {
        const originalHtml = state.submitter.dataset.submitOriginalHtml;
        if (originalHtml !== undefined) {
            state.submitter.innerHTML = originalHtml;
            delete state.submitter.dataset.submitOriginalHtml;
        }
        state.submitter.removeAttribute('aria-busy');
    }
    if (!state.bodyWasLocked) document.body.classList.remove('overflow-hidden');
    const overlay = document.getElementById('global-submit-overlay');
    if (overlay) {
        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
    }
}

function bootGlobalSubmitFetchBridge() {
    if (window.__adminSubmitFetchBridge) return;
    window.__adminSubmitFetchBridge = true;
    const nativeFetch = window.fetch.bind(window);
    window.fetch = (...args) => {
        let state = globalSubmitState;
        if (!state && isMutationMethod(getFetchMethod(args))) {
            startGuardForRecentAction();
            state = globalSubmitState;
        }
        if (!state?.asyncCompletion) return nativeFetch(...args);
        registerPendingRequest(state);
        try {
            return nativeFetch(...args).finally(() => releasePendingRequest(state));
        } catch (error) {
            releasePendingRequest(state);
            throw error;
        }
    };

    if (typeof window.XMLHttpRequest !== 'function') return;
    const nativeOpen = window.XMLHttpRequest.prototype.open;
    const nativeSend = window.XMLHttpRequest.prototype.send;
    window.XMLHttpRequest.prototype.open = function (method, ...args) {
        this.__adminSubmitMethod = String(method || 'GET').toUpperCase();
        return nativeOpen.call(this, method, ...args);
    };
    window.XMLHttpRequest.prototype.send = function (...args) {
        let state = globalSubmitState;
        if (!state && isMutationMethod(this.__adminSubmitMethod)) {
            startGuardForRecentAction();
            state = globalSubmitState;
        }
        if (!state?.asyncCompletion) return nativeSend.apply(this, args);
        registerPendingRequest(state);
        let released = false;
        const release = () => {
            if (released) return;
            released = true;
            releasePendingRequest(state);
        };
        this.addEventListener('loadend', release, { once: true });
        try {
            return nativeSend.apply(this, args);
        } catch (error) {
            release();
            throw error;
        }
    };
}

function getFetchMethod(args) {
    const [input, init] = args;
    if (init?.method) return String(init.method).toUpperCase();
    if (typeof window.Request === 'function' && input instanceof window.Request) return input.method.toUpperCase();
    return 'GET';
}

function startGuardForRecentAction() {
    if (globalSubmitState || !lastActionButtonClick) return;
    if (window.performance.now() - lastActionButtonClick.at > 1500) return;
    const button = lastActionButtonClick.button;
    if (!(button instanceof HTMLElement) || !button.isConnected) return;
    startGlobalSubmitGuard(button, false, true, button.form);
}

function registerPendingRequest(state) {
    if (state.finishTimerId !== null) {
        window.clearTimeout(state.finishTimerId);
        state.finishTimerId = null;
    }
    state.pendingRequests += 1;
}

function releasePendingRequest(state) {
    if (!globalSubmitState || globalSubmitState !== state) return;
    state.pendingRequests = Math.max(0, state.pendingRequests - 1);
    if (state.pendingRequests !== 0 || state.finishTimerId !== null) return;
    // Allow promise continuations and chained requests in the same submit
    // handler to register before ending the blocking state.
    state.finishTimerId = window.setTimeout(() => {
        state.finishTimerId = null;
        if (globalSubmitState === state && state.pendingRequests === 0) {
            finishGlobalSubmitGuard();
        }
    }, 50);
}

function getGlobalSubmitOverlay() {
    let overlay = document.getElementById('global-submit-overlay');
    if (overlay) return overlay;
    overlay = document.createElement('div');
    overlay.id = 'global-submit-overlay';
    overlay.hidden = true;
    overlay.setAttribute('aria-hidden', 'true');
    overlay.setAttribute('role', 'status');
    overlay.setAttribute('aria-live', 'polite');
    overlay.innerHTML = '<div style="display:inline-flex;align-items:center;gap:.75rem;max-width:92vw;padding:1rem 1.25rem;border:1px solid #dbeafe;border-radius:1rem;background:#fff;color:#0369a1;font-size:.875rem;font-weight:600;box-shadow:0 10px 30px rgba(15,23,42,.2)"><span style="width:1rem;height:1rem;border:2px solid #bae6fd;border-top-color:#0369a1;border-radius:9999px;animation:admin-submit-spin .8s linear infinite"></span><span>Guardando cambios…</span></div>';
    Object.assign(overlay.style, { position: 'fixed', inset: '0', zIndex: '2147483646', display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(15,23,42,.45)', backdropFilter: 'blur(2px)', cursor: 'wait', pointerEvents: 'all' });
    const style = document.createElement('style');
    style.textContent = '@keyframes admin-submit-spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(style);
    document.body.appendChild(overlay);
    return overlay;
}
