/* global HTMLTextAreaElement, Event */
import { resolveTranslatableFilePreviewUrl } from '../utils/fileUrl.js';
import { copyFieldToAll, copyDefaultToAll } from '../utils/translationCopy.js';

const toElement = (candidate) => {
    if (candidate instanceof HTMLElement) {
        return candidate;
    }
    if (typeof candidate === 'string') {
        const trimmed = candidate.trim();
        if (trimmed === '') {
            return null;
        }

        const normalizedId = trimmed.replace(/^#/, '');
        return document.querySelector(trimmed)
            || document.getElementById(normalizedId)
            || document.querySelector(`[id="${normalizedId.replace(/"/g, '\\"')}"]`);
    }
    return null;
};

const escapeRegExp = (value) => String(value || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const findTranslatableFileFieldComponent = (input) => {
    const container = input instanceof HTMLElement ? input.closest('[x-data*="translatableFileField"]') : null;
    const component = container?._x_dataStack?.[0];
    return component && typeof component.applyFile === 'function' ? component : null;
};

const findMediaReferenceFieldComponent = (input) => {
    const container = input instanceof HTMLElement ? input.closest('[x-data*="mediaReferenceField"]') : null;
    const component = container?._x_dataStack?.[0];
    return component && typeof component.applyReference === 'function' ? component : null;
};

const findRichTextEditorComponent = (input) => {
    const container = input instanceof HTMLElement ? input.closest('[x-data*="richTextEditor"]') : null;
    const component = container?._x_dataStack?.[0];
    return component && typeof component.applyContent === 'function' ? component : null;
};

const applyTargetValue = (targetInput, fileIdValue, fileUrlValue) => {
    if (!(targetInput instanceof HTMLInputElement)) {
        return;
    }

    targetInput.value = fileIdValue;
    targetInput.dispatchEvent(new Event('input', { bubbles: true }));

    const componentData = findTranslatableFileFieldComponent(targetInput);
    if (componentData) {
        componentData.applyFile(fileIdValue, fileUrlValue);
    }
};

const applyTranslatedText = (targetInput, translatedValue) => {
    if (!(targetInput instanceof HTMLInputElement || targetInput instanceof HTMLTextAreaElement)) {
        return;
    }

    targetInput.value = translatedValue;
    targetInput.dispatchEvent(new Event('input', { bubbles: true }));

    const richTextComponent = findRichTextEditorComponent(targetInput);
    if (richTextComponent) {
        richTextComponent.applyContent(translatedValue);
    }
};

const copyFileFieldValues = (
    sourceFileIdSelector,
    sourceFileUrlSelector,
    targetFileIdSelectors,
    targetFileUrlSelectors,
) => {
    const sourceFileId = toElement(sourceFileIdSelector);
    const sourceFileUrl = toElement(sourceFileUrlSelector);
    if (!(sourceFileId instanceof HTMLInputElement) || !(sourceFileUrl instanceof HTMLInputElement)) {
        console.warn('[langTabs] Could not find source file elements');
        return false;
    }

    const sourceFileIdValue = String(sourceFileId.value || '');
    const sourceFileUrlValue = String(sourceFileUrl.value || '');
    const resolvedFileUrl = resolveTranslatableFilePreviewUrl(sourceFileIdValue, sourceFileUrlValue);

    const fileIdInputs = Array.from(targetFileIdSelectors, toElement).filter((input) => input instanceof HTMLInputElement);
    const fileUrlInputs = Array.from(targetFileUrlSelectors, toElement).filter((input) => input instanceof HTMLInputElement);

    fileIdInputs.forEach((input) => {
        applyTargetValue(input, sourceFileIdValue, resolvedFileUrl);
    });

    fileUrlInputs.forEach((input) => {
        if (input.value === resolvedFileUrl) {
            return;
        }
        input.value = resolvedFileUrl;
        input.dispatchEvent(new Event('input', { bubbles: true }));

        const componentData = findTranslatableFileFieldComponent(input);
        if (componentData) {
            componentData.applyFile(sourceFileIdValue, resolvedFileUrl);
        }
    });

    return true;
};

export const copyLangTabsFileFieldToTargets = (
    sourceFileIdSelector,
    sourceFileUrlSelector,
    targetFileIdSelectors,
    targetFileUrlSelectors,
) => {
    return copyFileFieldValues(
        sourceFileIdSelector,
        sourceFileUrlSelector,
        targetFileIdSelectors,
        targetFileUrlSelectors,
    );
};

export const copyLangTabsFileFieldToAll = (
    sourceFileIdSelector,
    sourceFileUrlSelector,
    fieldKeyPattern,
) => {
    const fileIdPattern = new RegExp(`\\[block_data\\]\\[${escapeRegExp(fieldKeyPattern)}_file_id\\]$`);
    const fileUrlPattern = new RegExp(`\\[block_data\\]\\[${escapeRegExp(fieldKeyPattern)}_url\\]$`);
    const allBlockDataInputs = Array.from(document.querySelectorAll('input[name]'));
    const allFileIdInputs = allBlockDataInputs.filter((input) => fileIdPattern.test(input.name));
    const allFileUrlInputs = allBlockDataInputs.filter((input) => fileUrlPattern.test(input.name));
    return copyFileFieldValues(
        sourceFileIdSelector,
        sourceFileUrlSelector,
        allFileIdInputs,
        allFileUrlInputs,
    );
};

export const copyLangTabsMediaReferenceFieldToAll = (
    fieldKey,
    referenceValue,
) => {
    const normalizedFieldKey = String(fieldKey || '').trim();
    if (normalizedFieldKey === '') {
        return false;
    }

    const raw = (referenceValue && typeof referenceValue === 'object' && !Array.isArray(referenceValue))
        ? referenceValue
        : {};
    const normalizedReference = {
        source_kind: String(raw.source_kind ?? raw.sourceKind ?? 'hub_file'),
        file_id: String(raw.file_id ?? raw.fileId ?? ''),
        url: String(raw.url ?? raw.external_url ?? ''),
        preview_url: String(raw.preview_url ?? raw.previewUrl ?? raw.url ?? ''),
    };

    if (normalizedReference.source_kind === 'external_url') {
        normalizedReference.file_id = '';
    }

    // Matches the field's [source_kind]/[file_id]/[url] triple regardless of
    // what nests above it — `translations[N][block_data][fieldKey][...]` for
    // block config, or `translations[N][fieldKey][...]` for entry/page-level
    // fields like featured_image/og_image. fieldKey values are unique per
    // form, so a suffix match is unambiguous.
    const sourceKindPattern = new RegExp(`\\[${escapeRegExp(normalizedFieldKey)}\\]\\[source_kind\\]$`);
    const fileIdPattern = new RegExp(`\\[${escapeRegExp(normalizedFieldKey)}\\]\\[file_id\\]$`);
    const urlPattern = new RegExp(`\\[${escapeRegExp(normalizedFieldKey)}\\]\\[url\\]$`);
    const blockDataInputs = Array.from(document.querySelectorAll('input[name]'));
    const sourceKindInputs = blockDataInputs.filter((input) => sourceKindPattern.test(input.name));
    sourceKindInputs.forEach((sourceKindInput) => {
        const componentData = findMediaReferenceFieldComponent(sourceKindInput);
        if (componentData) {
            componentData.applyReference(normalizedReference);
            return;
        }

        if (!(sourceKindInput instanceof HTMLInputElement)) {
            return;
        }

        sourceKindInput.value = normalizedReference.source_kind;
        sourceKindInput.dispatchEvent(new Event('input', { bubbles: true }));
    });

    const fileIdInputs = blockDataInputs.filter((input) => fileIdPattern.test(input.name));
    fileIdInputs.forEach((fileIdInput) => {
        if (fileIdInput instanceof HTMLInputElement) {
            fileIdInput.value = normalizedReference.file_id;
            fileIdInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    const urlInputs = blockDataInputs.filter((input) => urlPattern.test(input.name));
    urlInputs.forEach((urlInput) => {
        if (urlInput instanceof HTMLInputElement) {
            urlInput.value = normalizedReference.url;
            urlInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    return sourceKindInputs.length > 0;
};

export const langTabs = (defaultId = 0, translateUrl = '', sourceLangCode = 'EN') => ({
    active: defaultId,
    translating: false,
    translateError: '',
    translatingAll: false,
    translateAllProgress: '',

    isActive(id) { return this.active === id; },
    setTab(id) { this.active = id; },

    async _translatePairs(targetLangCode, fieldPairs) {
        for (const pair of fieldPairs) {
            const sourceEl = document.querySelector(pair.from);
            const targetEl = document.querySelector(pair.to);
            if (!(sourceEl instanceof HTMLInputElement || sourceEl instanceof HTMLTextAreaElement)) continue;
            if (!(targetEl instanceof HTMLInputElement || targetEl instanceof HTMLTextAreaElement)) continue;
            const sourceText = sourceEl.value.trim();
            if (sourceText === '') continue;
            const url = new URL(translateUrl, window.location.origin);
            url.searchParams.set('text', sourceText);
            url.searchParams.set('source_lang', sourceLangCode.toUpperCase());
            url.searchParams.set('target_lang', targetLangCode.toUpperCase());
            const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const json = await res.json();
            if (json && typeof json.translated === 'string') {
                applyTranslatedText(targetEl, json.translated);
            } else if (json && json.error) {
                throw new Error(json.error);
            }
        }
    },

    async autoTranslate(targetLangCode, fieldPairs) {
        if (translateUrl === '' || this.translating || this.translatingAll) return;
        this.translating = true;
        this.translateError = '';
        try { await this._translatePairs(targetLangCode, fieldPairs); }
        catch (e) { this.translateError = e instanceof Error ? e.message : String(e); }
        finally { this.translating = false; }
    },

    async autoTranslateAll(targets) {
        if (translateUrl === '' || this.translating || this.translatingAll) return;
        this.translatingAll = true;
        this.translateError = '';
        try {
            for (let i = 0; i < targets.length; i++) {
                const { langCode, fieldPairs } = targets[i];
                this.translateAllProgress = langCode + ' (' + (i + 1) + '/' + targets.length + ')';
                await this._translatePairs(langCode, fieldPairs);
            }
            this.translateAllProgress = '';
        } catch (e) {
            this.translateError = e instanceof Error ? e.message : String(e);
            this.translateAllProgress = '';
        } finally { this.translatingAll = false; }
    },

    copyFieldToAll,
    copyDefaultToAll,

    copyFileFieldToAll: copyLangTabsFileFieldToAll,
    copyFileFieldToTargets: copyLangTabsFileFieldToTargets,
    copyMediaReferenceFieldToAll: copyLangTabsMediaReferenceFieldToAll,
});
