import store from '@/bootstrap/store.js';
import router from '%router';

import { addToast } from '@/plugins/toast.js';

import {
    useModal,
} from 'vue-final-modal';

import {
    addUser,
    resetUserPassword,
    confirmUserPassword,
    deactivateUser,
    addOrUpdateBibliographyItem,
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
    multieditAttributes,
    updateAttributeMetadata,
    addRole,
    patchRoleData,
    deleteRole,
    moveEntity,
} from '@/api.js';

import {
    can,
    userId,
    getUserBy,
    getEntityTypeAttributes,
    getTs,
    getRoleBy,
    handleDeletedEntity,
} from '@/helpers/helpers.js';

import About from '@/components/modals/system/About.vue';
import Discard from '@/components/modals/system/Discard.vue';
import Error from '@/components/modals/system/Error.vue';
import SaveScreencast from '@/components/modals/system/SaveScreencast.vue';
import ImportError from '@/components/modals/system/ImportError.vue';
import CsvPreviewer from '@/components/modals/csv/Preview.vue';
import CsvPicker from '@/components/modals/csv/Picker.vue';
import MapPicker from '@/components/modals/map/Picker.vue';
import MarkdownEditor from '@/components/modals/system/MarkdownEditor.vue';
import Changelog from '@/components/modals/system/Changelog.vue';
import UserInfo from '@/components/modals/user/UserInfo.vue';
import AddUser from '@/components/modals/user/Add.vue';
import ResetPassword from '@/components/modals/user/ResetPassword.vue';
import ConfirmPassword from '@/components/modals/user/ConfirmPassword.vue';
import DeactiveUser from '@/components/modals/user/Deactivate.vue';
import AccessControl from '@/components/modals/role/AccessControl.vue';
import AddRole from '@/components/modals/role/Add.vue';
import DeleteRole from '@/components/modals/role/Delete.vue';
import BibliographyItem from '@/components/modals/bibliography/Item.vue';
import DeleteBibliographyItem from '@/components/modals/bibliography/Delete.vue';
import BibliographyItemDetails from '@/components/modals/bibliography/Details.vue';
import AddEntity from '@/components/modals/entity/Add.vue';
import MoveEntity from '@/components/modals/entity/Move.vue';
import DeleteEntity from '@/components/modals/entity/Delete.vue';
import AddEntityType from '@/components/modals/entitytype/Add.vue';
import EditEntityType from '@/components/modals/entitytype/Edit.vue';
import DeleteEntityType from '@/components/modals/entitytype/Delete.vue';
import RemoveAttribute from '@/components/modals/entitytype/RemoveAttribute.vue';
import AddAttribute from '@/components/modals/attribute/Add.vue';
import EditAttribute from '@/components/modals/attribute/Edit.vue';
import MultiEditAttribute from '@/components/modals/attribute/MultiEdit.vue';
import EditSystemAttribute from '@/components/modals/attribute/EditSystem.vue';
import DeleteAttribute from '@/components/modals/attribute/Delete.vue';

export function showAbout() {
    const uid = `AboutModal-${getTs()}`;
    const modal = useModal({
        component: About,
        attrs: {
            name: uid,
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showDiscard(target, resetData, onBeforeConfirm) {
    const pushRoute = _ => {
        modal.destroy();
        resetData();
        router.push(target);
    };
    const uid = `Discard-${getTs()}`;
    const modal = useModal({
        component: Discard,
        attrs: {
            name: uid,
            onCancel(e) {
                modal.destroy();
            },
            onConfirm(e) {
                modal.destroy();
                pushRoute();
            },
            onSaveConfirm(e) {
                if(!!onBeforeConfirm) {
                    onBeforeConfirm().then(_ => {
                        modal.destroy();
                        pushRoute();
                    }).catch(e => {
                        modal.destroy();
                        return false;
                    });
                } else {
                    modal.destroy();
                    pushRoute();
                }
            },
        },
    });
    modal.open();
}

export function showError(data) {
    const uid = `ErrorModal-${getTs()}`;
    const modal = useModal({
        component: Error,
        attrs: {
            data: data,
            name: uid,
            onClosing(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showSaveScreencast(data) {
    const uid = `SaveScreencastModal-${getTs()}`;
    const modal = useModal({
        component: SaveScreencast,
        attrs: {
            data: data,
            name: uid,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showImportError(data) {
    const uid = `ImportErrorModa-${getTs()}`;
    const modal = useModal({
        component: ImportError,
        attrs: {
            data: data,
            name: uid,
            onClosing() {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showCsvPreviewer(data, onConfirm) {
    const uid = `CsvPreviewerModal-${getTs()}`;
    const modal = useModal({
        component: CsvPreviewer,
        attrs: {
            data: data,
            name: uid,
            onConfirm(e) {
                modal.destroy();
                if(!!onConfirm) {
                    onConfirm(e);
                }
            },
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showCsvColumnPicker(data, onConfirm) {
    const uid = `CsvPickerModal-${getTs()}`;
    const modal = useModal({
        component: CsvPicker,
        attrs: {
            max: data.max,
            force_max: data.force_max,
            selection: data.selection,
            name: uid,
            onConfirm(e) {
                modal.destroy();
                if(!!onConfirm) {
                    onConfirm(e);
                }
            },
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showMapPicker(data, onConfirm) {
    const uid = `MapPicker-${getTs()}`;
    const modal = useModal({
        component: MapPicker,
        attrs: {
            name: uid,
            data: data,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                if(!!onConfirm) {
                    onConfirm(e);
                }
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showMarkdownEditor(content, onConfirm, options = {}) {
    const uid = `MdEditorModal-${getTs()}`;
    const modal = useModal({
        component: MarkdownEditor,
        attrs: {
            name: uid,
            content: content,
            options: options,
            onConfirm(e) {
                if(!!onConfirm) {
                    onConfirm(e);
                }
                modal.destroy();
            },
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showChangelogModal(plugin) {
    const uid = `ChangelogModal-${getTs()}`;
    const modal = useModal({
        component: Changelog,
        attrs: {
            name: uid,
            plugin: plugin,
            onClosing(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showUserInfo(user) {
    const uid = `UserInfoModal-${getTs()}`;
    const modal = useModal({
        component: UserInfo,
        attrs: {
            name: uid,
            user: user,
            onClosing(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showAddUser(onAdded) {
    const uid = `AddUser-${getTs()}`;
    const modal = useModal({
        component: AddUser,
        attrs: {
            name: uid,
            modalId: uid,
            errors: {},
            onAdd(e) {
                if(!can('users_roles_create')) return;
                addUser(e).then(user => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addUser', user);
                    modal.destroy();
                }).catch(e => {
                    modal.patchOptions({
                        attrs: {
                            errors: e.response.data.errors,
                        },
                    });
                });
            },
            onCancel(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showResetPassword(id) {
    const uid = `ResetPassword-${getTs()}`;
    const modal = useModal({
        component: ResetPassword,
        attrs: {
            name: uid,
            modalId: uid,
            userId: id,
            onReset(e) {
                if(userId() != id && !can('users_roles_write')) return;

                resetUserPassword(id, e.password).then(_ => {
                    modal.destroy();
                    const user = getUserBy(id);
                    const msg = t('main.user.toasts.reset_password.message', {
                        name: user.name,
                        nickname: user.nickname,
                    });
                    const title = t('main.user.toasts.reset_password.title');
                    addToast(msg, title, {
                        channel: 'success',
                    });
                })
            },
            onCancel(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showConfirmPassword(id) {
    const uid = `ConfirmPassword-${getTs()}`;
    const modal = useModal({
        component: ConfirmPassword,
        attrs: {
            name: uid,
            modalId: uid,
            userId: id,
            onConfirm(e) {
                confirmUserPassword(id, e.password).then(_ => {
                    store.dispatch('updateUser', {
                        id: id,
                        login_attempts: null,
                    });
                    modal.destroy();
                })
            },
            onCancel(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showDeactivateUser(user, onDeactivated) {
    const uid = `DeactiveUser-${getTs()}`;
    const modal = useModal({
        component: DeactiveUser,
        attrs: {
            name: uid,
            user: user,
            onDeactivate(e) {
                if(!can('users_roles_delete')) {
                    modal.destroy();
                    return;
                }
                deactivateUser(user.id).then(data => {
                    if(!!onDeactivated) {
                        onDeactivated();
                    }
                    store.dispatch('deactivateUser', data);
                    modal.destroy();
                })
            },
            onCancel(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showAccessControlModal(roleId) {
    const uid = `AccessControl-${getTs()}`;
    const modal = useModal({
        component: AccessControl,
        attrs: {
            name: uid,
            roleId: roleId,
            onSave(e) {
                const data = {
                    permissions: e.permissions,
                    is_moderated: e.is_moderated,
                };
                patchRoleData(roleId, data).then(data => {
                    store.dispatch('updateRole', {
                        id: roleId,
                        permissions: data.permissions,
                        is_moderated: data.is_moderated,
                    });
                    const role = getRoleBy(roleId);
                    const msg = t('main.role.toasts.updated.msg', {
                        name: role.display_name
                    });
                    const title = t('main.role.toasts.updated.title');
                    addToast(msg, title, {
                        channel: 'success',
                    });
                });
            },
            onCancel(e) {
                modal.destroy();
            }
        },
    });
    modal.open();
}

export function showAddRole(onAdded) {
    const uid = `AddRole-${getTs()}`;
    const modal = useModal({
        component: AddRole,
        attrs: {
            name: uid,
            onAdd(e) {
                if(!can('users_roles_create')) return;
                addRole(e).then(role => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addRole', role);
                    modal.destroy();
                });
            },
            onCancel(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showDeleteRole(role, onDeleted) {
    const uid = `DeleteRole-${getTs()}`;
    const modal = useModal({
        component: DeleteRole,
        attrs: {
            name: uid,
            role: role,
            onConfirm(e) {
                if(!can('users_roles_delete')) return;

                deleteRole(role.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteRole', role);
                    modal.destroy();
                });
            },
            onCancel(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showBibliographyEntry(data, onSave) {
    const uid = `AddBibliographyEntry-${getTs()}`;
    const modal = useModal({
        component: BibliographyItem,
        attrs: {
            name: uid,
            data: data,
            onSave(e) {
                if(e.data.id && !can('bibliography_write')) return;
                if(!e.data.id && !can('bibliography_create')) return;

                const formData = e.data;
                const file = e.file;
                addOrUpdateBibliographyItem(formData, file).then(reData => {
                    // if id exists, it is an existing item
                    if(e.data.id) {
                        store.dispatch('updateBibliographyItem', {
                            id: e.data.id,
                            type: e.data.type.name,
                            fields: {
                                ...e.data.fields,
                                citekey: reData.citekey,
                                file: reData.file,
                                file_url: reData.file_url,
                            },
                        });
                    } else {
                        store.dispatch('addBibliographyItem', reData);
                    }

                    if(onSave) {
                        onSave(reData);
                    }

                    modal.destroy();
                });
            },
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showDeleteBibliographyEntry(entry, onDeleted) {
    const uid = `DeleteBibliographyEntry-${getTs()}`;
    const modal = useModal({
        component: DeleteBibliographyItem,
        attrs: {
            name: uid,
            data: entry,
            onDelete(e) {
                deleteBibliographyItem(entry.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted(entry);
                    }
                    store.dispatch('deleteBibliographyItem', entry);
                    modal.destroy();
                });
            },
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showLiteratureInfo(id, options) {
    const uid = `BibliographyItemDetails-${getTs()}`;
    const modal = useModal({
        component: BibliographyItemDetails,
        attrs: {
            name: uid,
            id: id,
            options: options,
            onClosing(e) {
                modal.destroy();
            },
        },
    });
    modal.open();
}

export function showAddEntity(parent = null, onAdded) {
    const uid = `AddEntity-${getTs()}`;
    const modal = useModal({
        component: AddEntity,
        attrs: {
            name: uid,
            parent: parent,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(entity) {
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
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function ShowMoveEntity(entity, onMoved) {
    const uid = `MoveEntity-${getTs()}`;
    const modal = useModal({
        component: MoveEntity,
        attrs: {
            name: uid,
            entity: entity,
            onCancel(e) {
                modal.destroy();
            },
            onConfirm(parentId) {
                moveEntity(entity.id, parentId).then(data => {
                    if(!!onMoved) {
                        onMoved(entity.id, parentId, data);
                    }
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showDeleteEntity(entityId, onDeleted) {
    const uid = `DeleteEntity-${getTs()}`;
    const modal = useModal({
        component: DeleteEntity,
        attrs: {
            name: uid,
            entityId: entityId,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm() {
                const entity = store.getters.entities[entityId];
                deleteEntity(entityId).then(_ => {
                    modal.destroy();
                    store.dispatch('deleteEntity', {
                        id: entityId,
                    });
                    handleDeletedEntity(entity).then(_ => {
                        if(!!onDeleted) {
                            onDeleted(entity);
                        }
                    });
                });
            },
        },
    });
    modal.open();
}

export function showAddEntityType(onAdded) {
    const uid = `AddEntityType-${getTs()}`;
    const modal = useModal({
        component: AddEntityType,
        attrs: {
            name: uid,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(entityType) {
                addEntityType(entityType).then(data => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addEntityType', data);
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showEditEntityType(entityType) {
    const uid = `EditEntityType-${getTs()}`;
    const modal = useModal({
        component: EditEntityType,
        attrs: {
            name: uid,
            entityType: entityType,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(editedProps) {
                patchEntityType(entityType.id, editedProps).then(_ => {
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showDeleteEntityType(entityType, metadata, onDeleted) {
    const uid = `DeleteEntityType-${getTs()}`;
    const modal = useModal({
        component: DeleteEntityType,
        attrs: {
            name: uid,
            entityType: entityType,
            metadata: metadata,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                deleteEntityType(entityType.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteEntityType', entityType);
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showEditAttribute(aid, etid, metadata) {
    const isSystem = metadata && metadata.is_system;
    const component = isSystem ? EditSystemAttribute : EditAttribute;
    const uid = `EditAttribute-${getTs()}`;
    const modal = useModal({
        component: component,
        attrs: {
            name: uid,
            attributeId: aid,
            entityTypeId: etid,
            metadata: metadata,
            attributeSelection: getEntityTypeAttributes(etid),
            onClosing(e) {
                modal.destroy();
            },
            async onConfirm(e) {
                if(isSystem) {
                    await updateAttributeMetadata(etid, aid, metadata.pivot.id, e);
                    modal.destroy();
                } else {
                    if(e.metadata) {
                        await updateAttributeMetadata(etid, aid, metadata.pivot.id, e.metadata);
                    }
                    if(e.dependency) {
                        await updateAttributeDependency(etid, aid, e.dependency);
                    }
                    modal.destroy();
                }
            },
        },
    });
    modal.open();
}

export function showMultiEditAttribute(entityIds, attributes) {
    const uid = `MultiEditAttribute-${getTs()}`;
    const modal = useModal({
        component: MultiEditAttribute,
        attrs: {
            name: uid,
            entityIds: entityIds,
            attributes: attributes,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                const values = e.values;
                const entries = [];
                for(let v in values) {
                    const aid = v;
                    const entry = {
                        value: values[aid],
                        attribute_id: aid,
                    };
                    entries.push(entry);
                }
                multieditAttributes(entityIds, entries).then(_ => {
                    store.dispatch('unsetTreeSelectionMode');
                    modal.destroy();
                    const title = t('main.entity.tree.multiedit.toast.saved.title');
                    const msg = t('main.entity.tree.multiedit.toast.saved.msg', {
                        attr_cnt: entries.length,
                        ent_cnt: entityIds.length,
                    });
                    addToast(msg, title, {
                        channel: 'success',
                    });
                });
            }
        },
    });
    modal.open();
}

export function showRemoveAttribute(etid, aid, id, metadata, onDeleted) {
    const uid = `RemoveAttribute-${getTs()}`;
    const modal = useModal({
        component: RemoveAttribute,
        attrs: {
            name: uid,
            attributeId: aid,
            metadata: metadata,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                removeEntityTypeAttribute(id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('removeEntityTypeAttribute', {
                        entity_type_id: etid,
                        attribute_id: id,
                    });
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showAddAttribute(onAdded) {
    const uid = `AddAttribute-${getTs()}`;
    const modal = useModal({
        component: AddAttribute,
        attrs: {
            name: uid,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(attribute) {
                addAttribute(attribute).then(data => {
                    if(!!onAdded) {
                        onAdded();
                    }
                    store.dispatch('addAttribute', data);
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showDeleteAttribute(attribute, metadata, onDeleted) {
    const uid = `DeleteAttribute-${getTs()}`;
    const modal = useModal({
        component: DeleteAttribute,
        attrs: {
            name: uid,
            attribute: attribute,
            metadata: metadata,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(e) {
                deleteAttribute(attribute.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    store.dispatch('deleteAttribute', attribute);
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function canShowReferenceModal(aid) {
    const attrValue = store.getters.entity.data[aid];
    return !!attrValue && attrValue.id;
}