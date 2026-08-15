/**
 * Alpine component for password fields with an accessible visibility toggle.
 *
 * Each password input gets its own state, so multiple fields (for example
 * password and confirmation) can be revealed independently.
 */
export const passwordToggle = () => ({
    visible: false,

    toggle() {
        this.visible = !this.visible;
    },
});
