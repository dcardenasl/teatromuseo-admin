import { describe, expect, it } from 'vitest';
import { passwordToggle } from './passwordToggle.js';

describe('passwordToggle', () => {
    it('starts with the password hidden', () => {
        expect(passwordToggle().visible).toBe(false);
    });

    it('toggles visibility independently for each instance', () => {
        const password = passwordToggle();
        const confirmation = passwordToggle();

        password.toggle();

        expect(password.visible).toBe(true);
        expect(confirmation.visible).toBe(false);

        password.toggle();
        expect(password.visible).toBe(false);
    });
});
