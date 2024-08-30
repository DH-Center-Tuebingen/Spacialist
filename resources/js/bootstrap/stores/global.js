import { defineStore } from 'pinia';

export const useGlobalStore = defineStore('global', {
    state: _ => ({
        appInitialized: false,
    }),
    getters: {
    },
    actions: {
    },
});

export default useGlobalStore;