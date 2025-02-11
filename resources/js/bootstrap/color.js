import useSystemStore from '@/bootstrap/stores/system.js';

export function getSupportedColorSets() {
    return useSystemStore().colorSets;
}
