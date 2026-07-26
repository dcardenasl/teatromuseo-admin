import { describe, it, expect } from 'vitest';
import { buildBlockTree } from './buildBlockTree.js';

describe('buildBlockTree', () => {
    it('builds a nested tree from a flat list using parent_instance_id', () => {
        const flat = [
            { id: 1, parent_instance_id: null, sort_order: 1 },
            { id: 2, parent_instance_id: 1, sort_order: 1 },
            { id: 3, parent_instance_id: 1, sort_order: 2 },
            { id: 4, parent_instance_id: null, sort_order: 2 },
        ];

        const tree = buildBlockTree(flat);

        expect(tree).toHaveLength(2);
        expect(tree[0].id).toBe(1);
        expect(tree[0]._children).toHaveLength(2);
        expect(tree[0]._children.map(c => c.id)).toEqual([2, 3]);
        expect(tree[1].id).toBe(4);
        expect(tree[1]._children).toEqual([]);
    });

    it('sorts top-level and child blocks by sort_order', () => {
        const flat = [
            { id: 1, parent_instance_id: null, sort_order: 2 },
            { id: 2, parent_instance_id: null, sort_order: 1 },
            { id: 3, parent_instance_id: 2, sort_order: 5 },
            { id: 4, parent_instance_id: 2, sort_order: 1 },
        ];

        const tree = buildBlockTree(flat);

        expect(tree.map(b => b.id)).toEqual([2, 1]);
        expect(tree[0]._children.map(c => c.id)).toEqual([4, 3]);
    });

    it('treats missing sort_order as 0', () => {
        const flat = [
            { id: 1, parent_instance_id: null },
            { id: 2, parent_instance_id: null, sort_order: -1 },
        ];

        const tree = buildBlockTree(flat);

        expect(tree.map(b => b.id)).toEqual([2, 1]);
    });

    it('returns an empty array for an empty input', () => {
        expect(buildBlockTree([])).toEqual([]);
    });
});
