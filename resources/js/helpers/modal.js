import store from '../bootstrap/store.js';

import {
    getTs
} from '../helpers/helpers.js';

import UserInfo from '../components/modals/users/UserInfo.vue';

export function showUserInfo(user) {
    const uid = `UserInfoModal-${getTs()}`;
    store.getters.vfm.show({
        component: UserInfo,
        bind: {
            name: uid,
            user: user,
        },
        on: {
            input(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}