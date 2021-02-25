import store from '../bootstrap/store.js';
import router from '../bootstrap/router.js';

import {
    deleteBibliographyItem,
    deleteEntityType,
} from '../api.js';

import {
    getTs
} from '../helpers/helpers.js';

import About from '../components/modals/system/About.vue';
import Discard from '../components/modals/system/Discard.vue';
import UserInfo from '../components/modals/users/UserInfo.vue';
import BibliographyItem from '../components/modals/bibliography/Item.vue';
import DeleteBibliographyItem from '../components/modals/bibliography/Delete.vue';
import AddEntityType from '../components/modals/entitytype/Add.vue';
import DeleteEntityType from '../components/modals/entitytype/Delete.vue';

export function showAbout() {
    const uid = `AboutModal-${getTs()}`;
    store.getters.vfm.show({
        component: About,
        bind: {
            name: uid,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showUserInfo(user) {
    const uid = `UserInfoModal-${getTs()}`;
    store.getters.vfm.show({
        component: UserInfo,
        bind: {
            name: uid,
            user: user,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showBibliographyEntry(data, onSuccess, onClose) {
    const uid = `AddBibliographyEntry-${getTs()}`;
    store.getters.vfm.show({
        component: BibliographyItem,
        bind: {
            name: uid,
            data: data,
            onSuccess: onSuccess,
            onClose: onClose,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showDeleteBibliographyEntry(entry, onDeleted) {
    const uid = `DeleteBibliographyEntry-${getTs()}`;
    store.getters.vfm.show({
        component: DeleteBibliographyItem,
        bind: {
            name: uid,
            data: entry,
        },
        on: {
            delete(e) {
                deleteBibliographyItem(entry.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteBibliographyItem', entry);
                    store.getters.vfm.hide(uid);
                });
            },
            closing(e) {
                store.getters.vfm.hide(uid);
            },
        }
    });
}

export function showAddEntityType(onAdded) {
    const uid = `AddEntityType-${getTs()}`;
    store.getters.vfm.show({
        component: AddEntityType,
        bind: {
            name: uid,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                const entityType = e;
                // TODO post api
                if(!!onAdded) {
                    onAdded();
                }
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showDeleteEntityType(entityType, metadata, onDeleted) {
    const uid = `DeleteEntityType-${getTs()}`;
    store.getters.vfm.show({
        component: DeleteEntityType,
        bind: {
            name: uid,
            entityType: entityType,
            metadata: metadata,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                deleteEntityType(entityType.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteEntityType', entityType);
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function showDiscard(target, resetData, onBeforeConfirm) {
    const pushRoute = _ => {
        store.getters.vfm.hide(uid);
        resetData();
        router.push(target);
    };
    const uid = `Discard-${getTs()}`;
    store.getters.vfm.show({
        component: Discard,
        bind: {
            name: uid,
        },
        on: {
            cancel(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                pushRoute();
            },
            saveConfirm(e) {
                if(!!onBeforeConfirm) {
                    onBeforeConfirm().then(data => {
                        pushRoute();
                    });
                } else {
                    pushRoute();
                }
            },
        }
    });
}