const EXTENSION_KINDS = Object.freeze({
    pdf: 'pdf',
    doc: 'word',
    docx: 'word',
    odt: 'word',
    rtf: 'word',
    xls: 'spreadsheet',
    xlsx: 'spreadsheet',
    ods: 'spreadsheet',
    csv: 'spreadsheet',
    tsv: 'spreadsheet',
    ppt: 'presentation',
    pptx: 'presentation',
    odp: 'presentation',
    zip: 'archive',
    rar: 'archive',
    '7z': 'archive',
    gz: 'archive',
    bz2: 'archive',
    tar: 'archive',
    tgz: 'archive',
    js: 'code',
    jsx: 'code',
    ts: 'code',
    tsx: 'code',
    css: 'code',
    scss: 'code',
    html: 'code',
    htm: 'code',
    xml: 'code',
    json: 'code',
    md: 'code',
    markdown: 'code',
    yaml: 'code',
    yml: 'code',
    sql: 'code',
    php: 'code',
    py: 'code',
    rb: 'code',
    java: 'code',
    c: 'code',
    h: 'code',
    cpp: 'code',
    txt: 'text',
    log: 'text',
    jpg: 'image',
    jpeg: 'image',
    png: 'image',
    gif: 'image',
    webp: 'image',
    avif: 'image',
    svg: 'image',
    bmp: 'image',
    tif: 'image',
    tiff: 'image',
    heic: 'image',
    mp3: 'audio',
    wav: 'audio',
    ogg: 'audio',
    m4a: 'audio',
    flac: 'audio',
    aac: 'audio',
    mp4: 'video',
    mov: 'video',
    webm: 'video',
    avi: 'video',
    mkv: 'video',
    mpeg: 'video',
    mpg: 'video',
});

const MIME_KINDS = [
    { kind: 'pdf', mimeTypes: ['application/pdf'] },
    { kind: 'word', mimeTypes: [
        'application/msword',
        'application/rtf',
        'application/vnd.ms-word',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ] },
    { kind: 'spreadsheet', mimeTypes: [
        'application/vnd.ms-excel',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/tab-separated-values',
    ] },
    { kind: 'presentation', mimeTypes: [
        'application/vnd.ms-powerpoint',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ] },
    { kind: 'archive', mimeTypes: [
        'application/gzip',
        'application/x-7z-compressed',
        'application/x-bzip2',
        'application/x-rar-compressed',
        'application/x-tar',
        'application/zip',
    ] },
    { kind: 'code', mimeTypes: [
        'application/javascript',
        'application/json',
        'application/sql',
        'application/xml',
        'text/css',
        'text/html',
        'text/javascript',
        'text/xml',
    ] },
    { kind: 'text', mimeTypes: ['text/markdown', 'text/plain'] },
];

const MIME_EXTENSIONS = Object.freeze({
    'application/pdf': 'pdf',
    'application/msword': 'doc',
    'application/rtf': 'rtf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
    'application/vnd.ms-excel': 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
    'text/csv': 'csv',
    'application/vnd.ms-powerpoint': 'ppt',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'pptx',
    'application/zip': 'zip',
    'application/gzip': 'gz',
    'application/json': 'json',
    'text/css': 'css',
    'text/html': 'html',
    'text/markdown': 'md',
    'text/plain': 'txt',
    'audio/mpeg': 'mp3',
    'audio/wav': 'wav',
    'audio/ogg': 'ogg',
    'video/mp4': 'mp4',
    'video/quicktime': 'mov',
    'video/webm': 'webm',
});

const PRESENTATIONS = Object.freeze({
    pdf: { icon: 'file-text', label: 'PDF' },
    word: { icon: 'file-text', label: 'DOC' },
    spreadsheet: { icon: 'file-spreadsheet', label: 'XLS' },
    presentation: { icon: 'presentation', label: 'PPT' },
    archive: { icon: 'file-archive', label: 'ZIP' },
    code: { icon: 'file-code', label: 'CODE' },
    text: { icon: 'file-text', label: 'TXT' },
    document: { icon: 'file-text', label: 'DOCUMENT' },
    image: { icon: 'file-image', label: 'IMAGE' },
    audio: { icon: 'music-2', label: 'AUDIO' },
    video: { icon: 'film', label: 'VIDEO' },
    generic: { icon: 'file-type', label: 'FILE' },
});

const CATEGORY_KINDS = Object.freeze({
    image: 'image',
    document: 'document',
    video: 'video',
    audio: 'audio',
});

const extensionFromFile = (file) => {
    const name = String(file?.original_name || file?.filename || file?.name || '').trim();
    const match = name.split(/[?#]/, 1)[0].match(/\.([a-z0-9]{1,16})$/i);
    return match ? match[1].toLowerCase() : '';
};

const kindFromMime = (mime) => {
    if (mime.startsWith('image/')) return 'image';
    if (mime.startsWith('audio/')) return 'audio';
    if (mime.startsWith('video/')) return 'video';

    const match = MIME_KINDS.find((entry) => entry.mimeTypes.includes(mime));
    return match?.kind || '';
};

const labelExtension = (extension, fallback) => {
    const normalized = String(extension || '').trim().toUpperCase();
    return normalized === '' ? fallback : normalized;
};

/**
 * Resolve a stable, safe visual identity for a file.
 *
 * MIME is preferred because it describes the detected content. The filename
 * extension and category are fallbacks for legacy/API responses that do not
 * carry complete metadata. Every returned theme is a fixed class name so API
 * data can never become a CSS class or icon name.
 *
 * @param {object|null|undefined} file
 * @returns {{kind: string, icon: string, label: string, extension: string, theme: string}}
 */
export const fileTypePresentation = (file) => {
    const mime = String(file?.mime_type || file?.mime || '').trim().toLowerCase();
    const extension = MIME_EXTENSIONS[mime] || extensionFromFile(file) || '';
    const kind = kindFromMime(mime)
        || EXTENSION_KINDS[extension]
        || CATEGORY_KINDS[String(file?.category || '').trim().toLowerCase()]
        || 'generic';
    const presentation = PRESENTATIONS[kind] || PRESENTATIONS.generic;
    const label = labelExtension(extension, presentation.label);

    return {
        kind,
        icon: presentation.icon,
        label,
        extension,
        theme: `file-type-${kind}`,
    };
};
