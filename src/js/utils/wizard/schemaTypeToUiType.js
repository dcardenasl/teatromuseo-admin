export function schemaTypeToUiType(schemaType, accept, primitive = '') {
    const rawPrimitive = String(primitive || '').trim().toLowerCase();
    if (rawPrimitive) return rawPrimitive;

    const type = String(schemaType || '').trim().toLowerCase();
    const accepts = String(accept || '').trim().toLowerCase();
    if (type === 'file') return accepts === 'image' || accepts === 'image/*' || accepts.startsWith('image/') ? 'image' : 'file';
    if (type === 'image') return 'image';
    if (type === 'media_reference') return 'media_reference';
    if (type === 'richtext' || type === 'rich_text') return 'richtext';
    if (type === 'string') return 'text';
    if (type === 'text') return 'textarea';
    if (type === 'number' || type === 'integer' || type === 'int') return 'number';
    if (type === 'boolean' || type === 'bool') return 'boolean';
    if (type === 'url') return 'url';
    if (type === 'select') return 'select';
    if (type === 'date' || type === 'datetime') return type;
    return 'unsupported';
}
