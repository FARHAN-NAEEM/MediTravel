import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('documentChecklist', (storageKey, documentIds) => ({
    storageKey: `ahc:document-checklist:v1:${storageKey}`,
    documentIds: documentIds.map(String),
    checkedIds: [],

    init() {
        try {
            const savedIds = JSON.parse(window.localStorage.getItem(this.storageKey) || '[]');

            if (Array.isArray(savedIds)) {
                this.checkedIds = savedIds
                    .map(String)
                    .filter((id) => this.documentIds.includes(id));
                this.persist();
            }
        } catch {
            this.checkedIds = [];
        }
    },

    isReady(id) {
        return this.checkedIds.includes(String(id));
    },

    toggle(id) {
        const normalizedId = String(id);

        this.checkedIds = this.isReady(normalizedId)
            ? this.checkedIds.filter((checkedId) => checkedId !== normalizedId)
            : [...this.checkedIds, normalizedId];

        this.persist();
    },

    reset() {
        this.checkedIds = [];
        this.persist();
    },

    persist() {
        try {
            window.localStorage.setItem(this.storageKey, JSON.stringify(this.checkedIds));
        } catch {
            // The checklist still works for the current page when storage is unavailable.
        }
    },

    get completedCount() {
        return this.checkedIds.length;
    },

    get totalCount() {
        return this.documentIds.length;
    },

    get remainingCount() {
        return Math.max(this.totalCount - this.completedCount, 0);
    },

    get percentReady() {
        return this.totalCount === 0
            ? 0
            : Math.round((this.completedCount / this.totalCount) * 100);
    },
}));

Alpine.start();
