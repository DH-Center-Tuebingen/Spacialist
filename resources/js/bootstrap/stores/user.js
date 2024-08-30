import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {
    state: _ => ({
        userLoggedIn: false,
    }),
    getters: {
        loggedIn: state => state.userLoggedIn,
    },
    actions: {
        login() {
            this.userLoggedIn = true;
        },
        logout() {
            this.userLoggedIn = false;
        },
    },
});

export default useUserStore;