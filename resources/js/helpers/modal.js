
import useAttributeStore from '@/bootstrap/stores/attribute.js';
import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
import useEntityStore from '@/bootstrap/stores/entity.js';
import useUserStore from '@/bootstrap/stores/user.js';

import router from '%router';

import { addToast } from '@/plugins/toast.js';

import {
    useModal,
} from 'vue-final-modal';

import {
    resetUserPassword,
    multieditAttributes,
    moveEntity,
} from '@/api.js';

import {
    can,
    userId,
    getEntityTypeAttributes,
    getTs,
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
                useUserStore().addUser(e).then(_ => {
                    if(!!onAdded) {
                        onAdded();
                    }
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
                    const user = useUserStore().getUserBy(id);
                    const msg = t('main.user.toasts.reset_password.message', {
                        name: user.name,
                        nickname: user.nickname,
                    });
                    const title = t('main.user.toasts.reset_password.title');
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

export function showConfirmPassword(id) {
    const uid = `ConfirmPassword-${getTs()}`;
    const modal = useModal({
        component: ConfirmPassword,
        attrs: {
            name: uid,
            modalId: uid,
            userId: id,
            onConfirm(e) {
                useUserStore().confirmOrUpdatePassword(id, e.password).then(_ => {
                    modal.destroy();
                });
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
                useUserStore().deactivateUser(user.id).then(_ => {
                    if(!!onDeactivated) {
                        onDeactivated();
                    }
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
                useUserStore().updateRole(roleId, data).then(role => {
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
                useUserStore().addRole(e).then(_ => {
                    if(!!onAdded) {
                        onAdded();
                    }
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

                useUserStore().deleteRole(role).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
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

                useBibliographyStore().createOrUpdate(e.data, e.file).then(data => {
                    if(onSave) {
                        onSave(data);
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
                useBibliographyStore().delete(entry.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted(entry);
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

export function showAddEntity(parent = null, onAdded, rank = -1) {
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
                if(rank != -1) {
                    entityData.rank = rank;
                }

                useEntityStore().create(entityData).then(node => {
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
                useEntityStore().delete(entityId).then(entity => {
                    modal.destroy();
                    if(!!onDeleted) {
                        onDeleted(entity);
                    }
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
                useEntityStore().addEntityType(entityType).then(_ => {
                    if(!!onAdded) {
                        onAdded();
                    }
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
                useEntityStore().patchEntityType(entityType.id, editedProps).then(_ => {
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
                useEntityStore().deleteEntityType(entityType.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showEditAttribute(aid, etid, metadata) {
    const uid = `EditAttribute-${getTs()}`;
    const modal = useModal({
        component: EditAttribute,
        attrs: {
            name: uid,
            attributeId: aid,
            entityTypeId: etid,
            metadata: metadata,
            onClosing(e) {
                modal.destroy();
            },
            async onConfirm(e) {
                const entityStore = useEntityStore();
                if(e.metadata) {
                    await entityStore.patchEntityMetadata(etid, aid, metadata.pivot.id, e.metadata);
                }
                if(e.dependency) {
                    await entityStore.updateDependency(etid, aid, e.dependency);
                }
                modal.destroy();
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
                    useEntityStore().setTreeSeletionMode(false);
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
                useEntityStore().removeEntityTypeAttribute(id, etid).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function showAddAttribute(onAdded, entityType = null) {
    const uid = `AddAttribute-${getTs()}`;
    const modal = useModal({
        component: AddAttribute,
        attrs: {
            name: uid,
            entityType: entityType,
            onClosing(e) {
                modal.destroy();
            },
            onConfirm(attribute) {
                useAttributeStore().addAttribute(attribute).then(data => {
                    if(!!onAdded) {
                        onAdded();
                    }
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
                useAttributeStore().deleteAttribute(attribute.id).then(_ => {
                    if(!!onDeleted) {
                        onDeleted();
                    }
                    modal.destroy();
                });
            },
        },
    });
    modal.open();
}

export function canShowReferenceModal(aid) {
    const attrValue = useEntityStore().selectedEntity?.data[aid];
    return !!attrValue && attrValue.id;
}