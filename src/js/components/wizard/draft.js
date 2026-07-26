// ── Draft persistence (localStorage) ─────────────────────────────
export const draft = {
    saveDraft() {
        try {
            localStorage.setItem('cms_wizard_draft', JSON.stringify({
                collectionId: this.selectedCollection?.id,
                step: this.currentStep,
                formData: this.formData,
                savedAt: new Date().toISOString(),
            }));
        } catch { /* ignore */ }
    },

    loadDraft() {
        try {
            const raw = localStorage.getItem('cms_wizard_draft');
            return raw ? JSON.parse(raw) : null;
        } catch { return null; }
    },

    clearDraft()   { try { localStorage.removeItem('cms_wizard_draft'); } catch { /* ignore */ } this.draft = null; },
    discardDraft() { this.clearDraft(); },

    resumeDraft() {
        if (!this.draft || !this.config) return;
        const col = (this.config.collections ?? []).find(c => c.id === this.draft.collectionId);
        if (!col) { this.discardDraft(); return; }
        this.selectedCollection = col;
        this.formData  = this.draft.formData ?? {};
        this.currentStep = this.draft.step ?? 0;
        this.draft = null;
        this.screen = 'steps';
    },
};
