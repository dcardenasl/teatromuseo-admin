export const jsonEditor = (initialValue = '{}') => ({
    value: initialValue,
    isValid: true,
    errorMsg: '',

    validate() {
        try { JSON.parse(this.value); this.isValid = true; this.errorMsg = ''; }
        catch (e) { this.isValid = false; this.errorMsg = e instanceof Error ? e.message : String(e); }
    },

    format() {
        try { this.value = JSON.stringify(JSON.parse(this.value), null, 2); this.isValid = true; this.errorMsg = ''; }
        catch (e) { this.isValid = false; this.errorMsg = e instanceof Error ? e.message : String(e); }
    },
});
