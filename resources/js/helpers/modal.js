import store from '../bootstrap/store.js';

import UserInfo from '../components/modals/users/UserInfo.vue';

export function showUserInfo(user) {
    store.getters.vfm.show({
        component: UserInfo,
        bind: {
            user: user,
        },
        on: {
            input(e) {
                store.getters.vfm.hide({
                    component: UserInfo,
                })
            }
        }
    });
}