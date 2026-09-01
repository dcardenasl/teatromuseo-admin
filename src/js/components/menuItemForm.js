export const menuItemForm = (initialLinkType = 'page', languageRows = []) => ({
    languages: Array.isArray(languageRows) ? languageRows : [],
        linkType: initialLinkType,
        categoryOptions: [],
        selectedColId: '',
        selectedCatId: '',
        activeCol: null,
        activeCat: null,
        init() {
            fetch('/admin/cms/menus/category-options')
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        this.categoryOptions = res.data;
                    }
                });
        },
        onCollectionChange() {
            this.selectedCatId = '';
            this.activeCat = null;
            this.activeCol = this.categoryOptions.find(c => c.id == this.selectedColId) || null;
        },
        onCategoryChange() {
            if (!this.activeCol) return;
            this.activeCat = this.activeCol.categories.find(cat => cat.id == this.selectedCatId) || null;
            if (this.activeCat) {
                this.autofillUrls();
            }
        },
        autofillUrls() {
            const languages = this.languages;
            Object.values(languages).forEach(lang => {
                const langId = lang.id;
                const languageIndex = Object.keys(languages).find(index => Number(languages[index].id) === Number(langId)) ?? langId;
                const colSlug = this.activeCol.translations[langId]?.slug || this.activeCol.key;
                const catSlug = this.activeCat.translations[langId]?.slug || '';
                if (colSlug && catSlug) {
                    const url = `/${colSlug}?category=${catSlug}`;
                    const urlInput = document.querySelector(`input[name='translations[${languageIndex}][custom_url]']`);
                    if (urlInput) {
                        urlInput.value = url;
                    }
                    const labelInput = document.querySelector(`input[name='translations[${languageIndex}][label]']`);
                    if (labelInput) {
                        labelInput.value = this.activeCat.translations[langId]?.name || '';
                    }
                }
            });
        }
});
