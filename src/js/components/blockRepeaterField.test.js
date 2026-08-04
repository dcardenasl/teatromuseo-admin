import { describe, expect, it } from 'vitest';
import { blockRepeaterField } from './blockRepeaterField.js';

describe('blockRepeaterField', () => {
    it('normalizes legacy scalar items into the repeater field shape', () => {
        const repeater = blockRepeaterField(
            ['Profesión', 'Presidente fundación'],
            { label: { type: 'string', label: 'Rol' } },
            'roles',
            0,
        );

        expect(repeater.items).toEqual([
            { label: 'Profesión' },
            { label: 'Presidente fundación' },
        ]);
    });

    it('adds a new item without changing existing fields', () => {
        const repeater = blockRepeaterField(
            [{ label: 'Actor' }],
            { label: { type: 'string', label: 'Rol' } },
            'roles',
            0,
        );

        repeater.addItem();

        expect(repeater.items).toEqual([{ label: 'Actor' }, { label: '' }]);
    });
});
