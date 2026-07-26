import { describe, expect, it } from 'vitest';
import { resolveCmsTranslationEditUrl } from './translationNavigation.js';

const routes = {
    page: '/admin/cms/pages',
    menu: '/admin/cms/menus',
    entry: '/admin/cms/entries',
    form: '/admin/cms/forms',
};

describe('resolveCmsTranslationEditUrl', () => {
    it('resolves a regular resource and preserves context', () => {
        expect(resolveCmsTranslationEditUrl(routes, { resource: 'page', resource_id: 7, language_id: 3 }))
            .toBe('/admin/cms/pages/7/edit?focus_lang=3');
    });

    it('resolves nested menu items using the owner id', () => {
        expect(resolveCmsTranslationEditUrl(routes, {
            resource: 'menu_item', resource_id: 9, language_id: 2, extra_data: { menu_id: 4 },
        })).toBe('/admin/cms/menus/4/items/9/edit?focus_lang=2');
    });

    it('fails closed when a nested owner is unavailable', () => {
        expect(resolveCmsTranslationEditUrl(routes, {
            resource: 'block_instance', resource_id: 9, language_id: 2, extra_data: { owner_type: 'unknown' },
        })).toBe('#');
    });

    it('carries return_to so the edit view can come back to the workbench', () => {
        expect(resolveCmsTranslationEditUrl(routes, { resource: 'page', resource_id: 7, language_id: 3 }, '/admin/cms/translations/audit?resource=page'))
            .toBe('/admin/cms/pages/7/edit?focus_lang=3&return_to=%2Fadmin%2Fcms%2Ftranslations%2Faudit%3Fresource%3Dpage');
    });

    it('omits return_to entirely when none is given (no empty param noise)', () => {
        expect(resolveCmsTranslationEditUrl(routes, { resource: 'page', resource_id: 7, language_id: 3 }, ''))
            .toBe('/admin/cms/pages/7/edit?focus_lang=3');
    });
});
