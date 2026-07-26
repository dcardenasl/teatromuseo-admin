export const richTextEditor = function richTextEditor(initialContent, fieldName, config = {}) {
    let editor = null;
    let toolbar = null;
    let onToolbarClick = null;
    const messages = {
        linkUrlPrompt: config.linkUrlPrompt || 'URL del enlace:',
        placeholder: config.placeholder || 'Escribe aquí…',
    };

    const syncHiddenInput = (component, value) => {
        const input = component.$refs.hiddenInput;
        if (input instanceof HTMLInputElement) {
            input.value = value;
            input.dispatchEvent(new window.Event('input', { bubbles: true }));
        }
    };

    const normalizeContent = (value) => {
        const text = String(value ?? '').trim();
        if (text === '') {
            return '';
        }

        return text.startsWith('<') ? text : `<p>${text}</p>`;
    };

    const teardown = (component) => {
        if (toolbar && onToolbarClick) toolbar.removeEventListener('click', onToolbarClick);
        toolbar = null;
        onToolbarClick = null;
        editor?.destroy();
        editor = null;
        if (component?.$refs?.editorEl instanceof HTMLElement) component.$refs.editorEl.innerHTML = '';
    };

    const runCommand = (action, value) => {
        if (!editor) return;
        const chain = editor.chain().focus();
        switch (action) {
            case 'bold': chain.toggleBold(); break;
            case 'italic': chain.toggleItalic(); break;
            case 'strike': chain.toggleStrike(); break;
            case 'code': chain.toggleCode(); break;
            case 'heading': chain.toggleHeading({ level: Number.parseInt(value || '0', 10) }); break;
            case 'bulletList': chain.toggleBulletList(); break;
            case 'orderedList': chain.toggleOrderedList(); break;
            case 'blockquote': chain.toggleBlockquote(); break;
            case 'hr': chain.setHorizontalRule(); break;
            case 'undo': chain.undo(); break;
            case 'redo': chain.redo(); break;
            case 'link': {
                const currentHref = editor.getAttributes('link').href || '';
                const url = window.prompt(messages.linkUrlPrompt, currentHref);
                if (url === null) return;
                if (url === '') chain.extendMarkRange('link').unsetLink();
                else chain.extendMarkRange('link').setLink({ href: url });
                break;
            }
            default: return;
        }
        chain.run();
    };

    return {
        inputName: fieldName || '',

        init() {
            if (typeof window.tiptap === 'undefined') return;
            const editorHost = this.$refs.editorEl;
            if (!(editorHost instanceof HTMLElement)) return;
            teardown(this);
            editorHost.innerHTML = '';
            const { Editor, StarterKit, Placeholder } = window.tiptap;
            editor = new Editor({
                element: editorHost,
                extensions: [
                    StarterKit.configure({ link: { openOnClick: false, autolink: true } }),
                    Placeholder.configure({ placeholder: messages.placeholder }),
                ],
                content: initialContent || '',
                onUpdate: ({ editor: currentEditor }) => { syncHiddenInput(this, currentEditor.getHTML()); },
            });
            syncHiddenInput(this, editor.getHTML());
            toolbar = this.$el.querySelector('[data-richtext-toolbar]');
            if (toolbar instanceof HTMLElement) {
                onToolbarClick = (event) => {
                    const target = event.target;
                    const button = target && typeof target.closest === 'function' ? target.closest('[data-richtext-action]') : null;
                    if (!(button instanceof HTMLElement) || !toolbar.contains(button)) return;
                    event.preventDefault();
                    runCommand(button.dataset.richtextAction || '', button.dataset.richtextLevel || button.dataset.richtextValue || '');
                };
                toolbar.addEventListener('click', onToolbarClick);
            }
        },

        applyContent(value) {
            const content = normalizeContent(value);
            if (editor) {
                editor.commands.setContent(content, false);
            }
            syncHiddenInput(this, content);
        },

        destroy() { teardown(this); },

        isActive(type, attrs) { return editor?.isActive(type, attrs) ?? false; },
    };
};
