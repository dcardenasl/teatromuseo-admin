/**
 * Resolve an audit row to its contextual editor URL.
 * @param {Record<string, string>} routes
 * @param {Record<string, unknown>} row
 * @param {string} returnTo Path to come back to after saving (echoed into a
 *   hidden `return_to` form field by the edit view; validated server-side by
 *   BaseWebController::resolveReturnUrl() before ever being redirected to).
 * @returns {string}
 */
export function resolveCmsTranslationEditUrl(routes, row, returnTo = '') {
    const resource = String(row?.resource ?? '');
    const resourceId = encodeURIComponent(String(row?.resource_id ?? ''));
    const extra = row?.extra_data && typeof row.extra_data === 'object' ? row.extra_data : {};
    let template = routes[resource] ? `${routes[resource]}/{id}/edit` : '';

    if (resource === 'menu_item') {
        const menuId = encodeURIComponent(String(extra.menu_id ?? ''));
        template = menuId === '' ? '' : `${routes.menu ?? ''}/${menuId}/items/{id}/edit`;
    } else if (resource === 'form_field') {
        const formId = encodeURIComponent(String(extra.form_id ?? ''));
        template = formId === '' ? '' : `${routes.form ?? ''}/${formId}/edit`;
    } else if (resource === 'block_instance') {
        const ownerType = String(extra.owner_type ?? '');
        const ownerRoute = ownerType === 'page' ? routes.page : ownerType === 'entry' ? routes.entry : '';
        const ownerId = encodeURIComponent(String(extra.owner_id ?? ''));
        const blockId = encodeURIComponent(String(row?.resource_id ?? ''));
        template = ownerRoute && ownerId !== '' && blockId !== '' ? `${ownerRoute}/${ownerId}/blocks/${blockId}/edit` : '';
    }

    if (template === '') return '#';

    const url = template.replace('{id}', resourceId);
    const params = { focus_lang: String(row?.language_id ?? '') };
    if (typeof returnTo === 'string' && returnTo !== '') {
        params.return_to = returnTo;
    }
    const query = new URLSearchParams(params);
    return `${url}?${query.toString()}`;
}
