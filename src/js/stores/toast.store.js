export const toastStore = {
    items: [],
    push(type, message) {
        const id = Date.now() + Math.random();
        this.items.push({ id, type, message });
        setTimeout(() => { this.remove(id); }, 5000);
    },
    remove(id) {
        this.items = this.items.filter((item) => item.id !== id);
    }
};
