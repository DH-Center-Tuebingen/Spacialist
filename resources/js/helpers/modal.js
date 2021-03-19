import store from '../bootstrap/store.js';
import router from '../bootstrap/router.js';

import {
    addUser,
    deactivateUser,
    deleteBibliographyItem,
    deleteEntityType,
} from '../api.js';

import {
    can,
    getTs,
} from '../helpers/helpers.js';

import About from '../components/modals/system/About.vue';
import Discard from '../components/modals/system/Discard.vue';
import UserInfo from '../components/modals/user/UserInfo.vue';
import AddUser from '../components/modals/user/Add.vue';
import DeactiveUser from '../components/modals/user/Deactivate.vue';
import BibliographyItem from '../components/modals/bibliography/Item.vue';
import DeleteBibliographyItem from '../components/modals/bibliography/Delete.vue';
import AddEntityType from '../components/modals/entitytype/Add.vue';
import DeleteEntityType from '../components/modals/entitytype/Delete.vue';
import AddAttribute from '../components/modals/attribute/Add.vue';

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

export function showAddUser(onAdded) {
    const uid = `AddUser-${getTs()}`;
    store.getters.vfm.show({
        component: AddUser,
        bind: {
            name: uid,
        },
        on: {
            add(e) {
                if(!can('create_users')) return;
                addUser(e).then(user => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addUser', user);
                    store.getters.vfm.hide(uid);
                });
            },
            cancel(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showDeactivateUser(user, onDeactivated) {
    const uid = `DeactiveUser-${getTs()}`;
    store.getters.vfm.show({
        component: DeactiveUser,
        bind: {
            name: uid,
            user: user,
        },
        on: {
            deactivate(e) {
                if(!can('delete_users')) {
                    store.getters.vfm.hide(uid);
                    return;
                }
                deactivateUser(user.id).then(data => {
                    if(!!onDeactivated) {
                        onDeactivated();
                    }
                    store.dispatch('deactivateUser', data);
                    store.getters.vfm.hide(uid);
                })
            },
            cancel(e) {
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

export function showAddAttribute(onAdded) {
    const uid = `AddAttribute-${getTs()}`;
    store.getters.vfm.show({
        component: AddAttribute,
        bind: {
            name: uid,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                if(!!onAdded) {
                    onAdded();
                }
                store.getters.vfm.hide(uid);
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
                    onBeforeConfirm().then(_ => {
                        pushRoute();
                    }).catch(e => {
                        store.getters.vfm.hide(uid);
                        return false;
                    });
                } else {
                    pushRoute();
                }
            },
        }
    });
}