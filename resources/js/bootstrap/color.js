import store from '@/bootstrap/store.js';

export function getSupportedColorSets() {
    return store.getters.colorSets;
}
