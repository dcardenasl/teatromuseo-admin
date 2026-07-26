/* global AbortController, HTMLSelectElement, Event */
import { devError } from './dev.js';

export const slugify = (value) => String(value || '')
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-')
    .slice(0, 255);

const normalizeSlugValue = (value, maxLength = 255) => slugify(value).slice(0, maxLength);

export const bootSlugFields = () => {
    document.querySelectorAll('input[data-slug-source]').forEach((slugInput) => {
        if (!(slugInput instanceof HTMLInputElement)) return;

        const sourceSelector = slugInput.dataset.slugSource || '';
        const sourceInput = sourceSelector === '' ? null : document.querySelector(sourceSelector);
        const regenerateButton = slugInput.closest('[data-slug-field]')?.querySelector('[data-slug-regenerate]');
        const checkUrl = slugInput.dataset.slugCheckUrl || '';
        const languageIdAttribute = slugInput.dataset.slugLanguageId || '';
        const languageSelector = slugInput.dataset.slugLanguageSelector || '';
        const currentId = slugInput.dataset.slugCurrentId || '';
        const invalidMessage = slugInput.dataset.slugInvalidMessage || '';
        const statusIcons = slugInput.closest('[data-slug-field]')?.querySelectorAll('[data-slug-status]') || [];
        const languageInput = languageSelector === '' ? null : document.querySelector(languageSelector);

        if (!(sourceInput instanceof HTMLInputElement)) return;

        let manual = slugInput.value.trim() !== '' && slugInput.value.trim() !== slugify(sourceInput.value);
        let availabilityTimer = 0;
        let availabilityRequest = null;

        const showStatus = (status) => {
            slugInput.dataset.slugAvailability = status || '';
            window.dispatchEvent(new CustomEvent('slug-availability-changed', {
                detail: {
                    id: slugInput.id || '',
                    status: status || '',
                    value: slugInput.value.trim(),
                },
            }));
            statusIcons.forEach((icon) => {
                if (!(icon instanceof HTMLElement)) return;
                const active = icon.dataset.slugStatus === status;
                icon.classList.toggle('hidden', !active);
                icon.classList.toggle('flex', active);
            });
        };

        const checkAvailability = () => {
            window.clearTimeout(availabilityTimer);
            if (availabilityRequest !== null) {
                availabilityRequest.abort();
                availabilityRequest = null;
            }

            const slug = slugInput.value.trim();
            const resolvedLanguageId = String(slugInput.dataset.slugLanguageId || languageIdAttribute || '').trim();
            if (resolvedLanguageId === '') {
                showStatus('');
                slugInput.setCustomValidity('');
                return;
            }

            if (checkUrl === '' || slug.length < 2 || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
                showStatus('');
                slugInput.setCustomValidity(invalidMessage || 'Invalid slug format.');
                return;
            }

            availabilityTimer = window.setTimeout(() => {
                const url = new URL(checkUrl, window.location.origin);
                url.searchParams.set('slug', slug);
                if (languageInput instanceof HTMLInputElement || languageInput instanceof HTMLSelectElement) {
                    const languageId = String(languageInput.value || '').trim();
                    if (languageId !== '') url.searchParams.set('language_id', languageId);
                } else {
                    url.searchParams.set('language_id', resolvedLanguageId);
                }
                if (currentId !== '') url.searchParams.set('current_id', currentId);

                const controller = new AbortController();
                availabilityRequest = controller;
                showStatus('checking');

                fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: controller.signal })
                    .then((response) => response.ok ? response.json() : Promise.reject(new Error(String(response.status))))
                    .then((payload) => {
                        const available = payload && payload.available === true;
                        showStatus(available ? 'available' : 'unavailable');
                        if (available) {
                            slugInput.setCustomValidity('');
                            return;
                        }
                        const unavailableIcon = Array.from(statusIcons).find((icon) =>
                            icon instanceof HTMLElement && icon.dataset.slugStatus === 'unavailable'
                        );
                        slugInput.setCustomValidity(unavailableIcon instanceof HTMLElement ? unavailableIcon.title : 'Slug unavailable');
                    })
                    .catch((error) => {
                        if (error && error.name === 'AbortError') return;
                        showStatus('');
                        slugInput.setCustomValidity('');
                        devError('Slug availability check failed', error);
                    })
                    .finally(() => {
                        if (availabilityRequest === controller) availabilityRequest = null;
                    });
            }, 350);
        };

        const syncFromSource = () => {
            if (manual) return;
            slugInput.value = slugify(sourceInput.value);
            slugInput.dispatchEvent(new Event('input', { bubbles: true }));
            checkAvailability();
        };

        sourceInput.addEventListener('input', syncFromSource);
        slugInput.addEventListener('input', () => {
            const normalized = slugify(slugInput.value);
            manual = normalized !== '' && normalized !== slugify(sourceInput.value);
            slugInput.value = normalized;
            checkAvailability();
        });

        if (regenerateButton instanceof HTMLButtonElement) {
            regenerateButton.addEventListener('click', () => {
                manual = false;
                syncFromSource();
                slugInput.focus();
            });
        }

        syncFromSource();
        checkAvailability();
    });

    document.querySelectorAll('input[data-auto-slug-source]').forEach((slugInput) => {
        if (!(slugInput instanceof HTMLInputElement)) return;

        const sourceSelector = slugInput.dataset.autoSlugSource || '';
        const sourceInput = sourceSelector === '' ? null : document.querySelector(sourceSelector);

        if (!(sourceInput instanceof HTMLInputElement)) return;

        const parsedMaxLength = Number.parseInt(slugInput.dataset.autoSlugMaxlength || '', 10);
        const maxLength = Number.isFinite(parsedMaxLength) && parsedMaxLength > 0 ? parsedMaxLength : 255;

        let manual = slugInput.value.trim() !== '' && slugInput.value.trim() !== normalizeSlugValue(sourceInput.value, maxLength);

        const syncFromSource = () => {
            if (manual) return;
            slugInput.value = normalizeSlugValue(sourceInput.value, maxLength);
        };

        sourceInput.addEventListener('input', syncFromSource);
        slugInput.addEventListener('input', () => {
            const normalized = normalizeSlugValue(slugInput.value, maxLength);
            manual = normalized !== '' && normalized !== normalizeSlugValue(sourceInput.value, maxLength);
            slugInput.value = normalized;
        });

        slugInput.addEventListener('blur', () => {
            if (slugInput.value.trim() === '') {
                manual = false;
                syncFromSource();
            }
        });

        syncFromSource();
    });
};
