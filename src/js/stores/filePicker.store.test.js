import { describe, expect, it, beforeEach, vi } from 'vitest';

vi.stubGlobal('document', { documentElement: { dataset: { env: 'test' } } });
vi.stubGlobal('window', {});
const { filePickerStore } = await import('./filePicker.store.js');

describe('filePickerStore', () => {
    beforeEach(() => {
        filePickerStore.allFiles = [];
        filePickerStore.files = [];
        filePickerStore.search = '';
        filePickerStore.filterType = '';
        filePickerStore.pagination = { current_page: 1, last_page: 1, total_items: 0, per_page: 24 };
    });

    it('paginates the manifest locally without another data request', () => {
        filePickerStore.allFiles = Array.from({ length: 49 }, (_, index) => ({
            id: index + 1,
            original_name: `image-${index + 1}.jpg`,
            category: 'image',
        }));

        filePickerStore.applyLocalFilters(3);

        expect(filePickerStore.pagination.last_page).toBe(3);
        expect(filePickerStore.files).toHaveLength(1);
        expect(filePickerStore.files[0].id).toBe(49);
    });

    it('filters the in-memory manifest without fetching', () => {
        filePickerStore.allFiles = [
            { id: 1, original_name: 'obra.jpg', category: 'image' },
            { id: 2, original_name: 'contrato.pdf', category: 'document' },
        ];
        filePickerStore.search = 'obra';
        filePickerStore.filterType = 'image';

        filePickerStore.applyLocalFilters(1);

        expect(filePickerStore.files.map((file) => file.id)).toEqual([1]);
        expect(filePickerStore.pagination.total_items).toBe(1);
    });
});
