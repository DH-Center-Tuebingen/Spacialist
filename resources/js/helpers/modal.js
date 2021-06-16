import store from '../bootstrap/store.js';
import router from '../bootstrap/router.js';

import {
    addUser,
    deactivateUser,
    deleteBibliographyItem,
    addEntity,
    addEntityType,
    deleteEntityType,
    addAttribute,
    deleteAttribute,
    deleteEntity,
    removeEntityTypeAttribute,
    patchEntityType,
    updateAttributeDependency,
    addRole,
    deleteRole,
} from '../api.js';

import {
    can,
    getEntityTypeAttributes,
    getTs,
    handleDeletedEntity,
} from '../helpers/helpers.js';

import About from '../components/modals/system/About.vue';
import Discard from '../components/modals/system/Discard.vue';
import Error from '../components/modals/system/Error.vue';
import CsvPreviewer from '../components/modals/csv/Preview.vue';
import CsvPicker from '../components/modals/csv/Picker.vue';
import MapPicker from '../components/modals/map/Picker.vue';
import UserInfo from '../components/modals/user/UserInfo.vue';
import AddUser from '../components/modals/user/Add.vue';
import DeactiveUser from '../components/modals/user/Deactivate.vue';
import AddRole from '../components/modals/role/Add.vue';
import DeleteRole from '../components/modals/role/Delete.vue';
import BibliographyItem from '../components/modals/bibliography/Item.vue';
import DeleteBibliographyItem from '../components/modals/bibliography/Delete.vue';
import AddEntity from '../components/modals/entity/Add.vue';
import DeleteEntity from '../components/modals/entity/Delete.vue';
import AddEntityType from '../components/modals/entitytype/Add.vue';
import EditEntityType from '../components/modals/entitytype/Edit.vue';
import DeleteEntityType from '../components/modals/entitytype/Delete.vue';
import RemoveAttribute from '../components/modals/entitytype/RemoveAttribute.vue';
import AddAttribute from '../components/modals/attribute/Add.vue';
import EditAttribute from '../components/modals/attribute/Edit.vue';
import DeleteAttribute from '../components/modals/attribute/Delete.vue';

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
                if (!!onBeforeConfirm) {
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

export function showError(data) {
    const uid = `ErrorModal-${getTs()}`;
    store.getters.vfm.show({
        component: Error,
        bind: {
            data: data,
            name: uid,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showCsvPreviewer(data, onConfirm) {
    const uid = `CsvPreviewerModal-${getTs()}`;
    store.getters.vfm.show({
        component: CsvPreviewer,
        bind: {
            data: data,
            name: uid,
        },
        on: {
            confirm(e) {
                store.getters.vfm.hide(uid);
                if(!!onConfirm) {
                    onConfirm(e);
                }
            },
            closing(e) {
                store.getters.vfm.hide(uid);
            },
        }
    });
}

export function showCsvColumnPicker(data, onConfirm) {
    const uid = `CsvPickerModal-${getTs()}`;
    store.getters.vfm.show({
        component: CsvPicker,
        bind: {
            max: data.max,
            force_max: data.force_max,
            selection: data.selection,
            name: uid,
        },
        on: {
            confirm(e) {
                store.getters.vfm.hide(uid);
                if(!!onConfirm) {
                    onConfirm(e);
                }
            },
            closing(e) {
                store.getters.vfm.hide(uid);
            },
        }
    });
}

export function showMapPicker(data, onConfirm) {
    const uid = `MapPicker-${getTs()}`;
    store.getters.vfm.show({
        component: MapPicker,
        bind: {
            name: uid,
            data: data,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                if(!!onConfirm) {
                    onConfirm(e);
                }
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

export function showAddRole(onAdded) {
    const uid = `AddRole-${getTs()}`;
    store.getters.vfm.show({
        component: AddRole,
        bind: {
            name: uid,
        },
        on: {
            add(e) {
                if(!can('add_edit_role')) return;
                addRole(e).then(role => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addRole', role);
                    store.getters.vfm.hide(uid);
                });
            },
            cancel(e) {
                store.getters.vfm.hide(uid);
            }
        }
    });
}

export function showDeleteRole(role, onDeleted) {
    const uid = `DeleteRole-${getTs()}`;
    store.getters.vfm.show({
        component: DeleteRole,
        bind: {
            name: uid,
            role: role,
        },
        on: {
            confirm(e) {
                if(!can('delete_role')) return;

                deleteRole(role.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteRole', role);
                    store.getters.vfm.hide(uid);
                });
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

export function ShowAddEntity(parent = null, onAdded) {
    const uid = `AddEntity-${getTs()}`;
    store.getters.vfm.show({
        component: AddEntity,
        bind: {
            name: uid,
            parent: parent,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(entity) {
                const entityData = {
                    type_id: entity.type.id,
                    parent_id: entity.parent_id,
                    name: entity.name,
                };
                addEntity(entityData).then(data => {
                    const node = store.dispatch('addEntity', data);
                    if(!!onAdded) {
                        onAdded(node);
                    }
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function showDeleteEntity(entityId, onDeleted) {
    const uid = `DeleteEntity-${getTs()}`;
    store.getters.vfm.show({
        component: DeleteEntity,
        bind: {
            name: uid,
            entityId: entityId,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm() {
                const entity = store.getters.entities[entityId];
                deleteEntity(entityId).then(_ => {
                    store.getters.vfm.hide(uid);
                    store.dispatch('deleteEntity', {
                        id: entityId,
                    });
                    handleDeletedEntity(entity).then(_ => {
                        if(!!onDeleted) {
                            onDeleted(entity);
                        }
                    });
                });
            }
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
            confirm(entityType) {
                addEntityType(entityType).then(data => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addEntityType', data);
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function showEditEntityType(entityType) {
    const uid = `EditEntityType-${getTs()}`;
    store.getters.vfm.show({
        component: EditEntityType,
        bind: {
            name: uid,
            entityType: entityType,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(editedProps) {
                patchEntityType(entityType.id, editedProps).then(_ => {
                    store.getters.vfm.hide(uid);
                });
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

export function showEditAttribute(aid, etid) {
    const uid = `EditAttribute-${getTs()}`;
    store.getters.vfm.show({
        component: EditAttribute,
        bind: {
            name: uid,
            attributeId: aid,
            entityTypeId: etid,
            attributeSelection: getEntityTypeAttributes(etid),
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                updateAttributeDependency(etid, aid, e).then(_ => {
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function showRemoveAttribute(etid, aid, metadata, onDeleted) {
    const uid = `RemoveAttribute-${getTs()}`;
    store.getters.vfm.show({
        component: RemoveAttribute,
        bind: {
            name: uid,
            attributeId: aid,
            metadata: metadata,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                removeEntityTypeAttribute(etid, aid).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('removeEntityTypeAttribute', {
                        entity_type_id: etid,
                        attribute_id: aid,
                    });
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
            confirm(attribute) {
                addAttribute(attribute).then(data => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addAttribute', data);
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function showDeleteAttribute(attribute, metadata, onDeleted) {
    const uid = `DeleteAttribute-${getTs()}`;
    store.getters.vfm.show({
        component: DeleteAttribute,
        bind: {
            name: uid,
            attribute: attribute,
            metadata: metadata,
        },
        on: {
            closing(e) {
                store.getters.vfm.hide(uid);
            },
            confirm(e) {
                deleteAttribute(attribute.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteAttribute', attribute);
                    store.getters.vfm.hide(uid);
                });
            }
        }
    });
}

export function canShowReferenceModal(aid) {
    const attrValue = store.getters.entity.data[aid];
    return !!attrValue && attrValue.id;
}