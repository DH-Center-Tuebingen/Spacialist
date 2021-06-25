<template>
    <div class="d-flex flex-column h-100">
        <h4 class="mb-0">
            {{ t('global.roles') }}
            <button type="button" class="btn btn-outline-success" @click="showAddRoleModal()" :disabled="!can('add_edit_role')">
                <i class="fas fa-fw fa-plus"></i> {{ t('main.role.add_button') }}
            </button>
        </h4>
        <div class="table-responsive flex-grow-1">
            <table class="table table-striped table-hover table-light mb-0" v-if="state.dataInitialized">
                <thead class="sticky-top">
                    <tr>
                        <th>{{ t('global.name') }}</th>
                        <th>{{ t('global.display_name') }}</th>
                        <th>{{ t('global.description') }}</th>
                        <th>{{ t('global.permissions') }}</th>
                        <th>{{ t('global.created_at') }}</th>
                        <th>{{ t('global.updated_at') }}</th>
                        <th>{{ t('global.options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in state.roleList" :key="role.id">
                        <td>
                            {{ role.name }}
                        </td>
                        <td>
                            <input
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(role.id, 'display_name'))"
                                :name="`displayname_${role.id}`"
                                v-model="v.fields[role.id].display_name.value"
                                @input="e => v.fields[role.id].display_name.handleInput(e)" />

                            <div class="invalid-feedback">
                                <span v-for="(msg, i) in getErrors(role.id, 'display_name')" :key="i">
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <input
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(role.id, 'description'))"
                                :name="`description_${role.id}`"
                                v-model="v.fields[role.id].description.value"
                                @input="e => v.fields[role.id].description.handleInput(e)" />

                            <div class="invalid-feedback">
                                <span v-for="(msg, i) in getErrors(role.id, 'description')" :key="i">
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <multiselect
                                v-model="v.fields[role.id].permissions.value"
                                :class="getClassByValidation(getErrors(role.id, 'permissions'))"
                                :name="`permissions_${role.id}`"
                                :object="true"
                                :label="'display_name'"
                                :track-by="'display_name'"
                                :valueProp="'id'"
                                :mode="'tags'"
                                :disabled="!can('add_remove_permission')"
                                :options="state.permissions"
                                :placeholder="t('main.role.add_permission_placeholder')"
                                @input="v.fields[role.id].permissions.handleInput">
                            </multiselect>

                            <div class="invalid-feedback">
                                <span v-for="(msg, i) in getErrors(role.id, 'permissions')" :key="i">
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ date(role.created_at) }}
                        </td>
                        <td>
                            {{ date(role.updated_at) }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <span :id="`role-options-dropdown-${role.id}`" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                    <sup class="notification-info" v-if="roleDirty(role.id)">
                                        <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                    </sup>
                                </span>
                                <div class="dropdown-menu" :aria-labelledby="`role-options-dropdown-${role.id}`">
                                    <a class="dropdown-item" href="#" v-if="roleDirty(role.id)" :disabled="!can('add_remove_permission')" @click.prevent="patchRole(role.id)">
                                        <i class="fas fa-fw fa-check text-success"></i> {{ t('global.save') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="deleteRole(role.id)" :disabled="!can('delete_role')">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { onBeforeRouteLeave } from 'vue-router';
    import { useI18n } from 'vue-i18n';
    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import store from '../bootstrap/store.js';

    import { useToast } from '../plugins/toast.js';

    import {
        showDiscard,
        showAddRole,
        showDeleteRole,
    } from '../helpers/modal.js';
    import {
        can,
        getClassByValidation,
        getErrorMessages,
        getRoleBy,
    } from '../helpers/helpers.js';
    import {
        date,
    } from '../helpers/filters.js';
import { patchRoleData } from '../api.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const roleDirty = id => {
                return v.fields[id].display_name.meta.dirty ||
                    v.fields[id].description.meta.dirty ||
                    v.fields[id].permissions.meta.dirty;
            };
            const roleValid = id => {
                return v.fields[id].display_name.meta.valid ||
                    v.fields[id].description.meta.valid ||
                    v.fields[id].permissions.meta.valid;
            };
            const resetRole = id => {
                v.fields[id].display_name.reset();
                v.fields[id].description.reset();
                v.fields[id].permissions.reset();
            };
            const resetRoleMeta = id => {
                v.fields[id].display_name.reset({
                    value: v.fields[id].display_name.value,
                });
                v.fields[id].description.reset({
                    value: v.fields[id].description.value,
                });
                v.fields[id].permissions.reset({
                    value: v.fields[id].permissions.value,
                });
            };
            const patchRole = async id => {
                if(!roleDirty(id) || !roleValid(id) || !can('add_edit_role')) {
                    return;
                }

                const data = {};
                if(v.fields[id].permissions.meta.dirty) {
                    data.permissions = v.fields[id].permissions.value.map(p => p.id);
                }
                if(v.fields[id].display_name.meta.dirty) {
                    data.display_name = v.fields[id].display_name.value;
                }
                if(v.fields[id].description.meta.dirty) {
                    data.description = v.fields[id].description.value;
                }

                return await patchRoleData(id, data).then(data => {
                    state.errors[id] = {};
                    resetRoleMeta(id);
                    store.dispatch('updateRole', {
                        id: data.id,
                        display_name: data.display_name,
                        description: data.description,
                        permissions: data.permissions,
                        updated_at: data.updated_at,
                    });
                    const role = getRoleBy(id);
                    const msg = t('main.role.toasts.updated.msg', {
                        name: role.display_name
                    });
                    const title = t('main.role.toasts.updated.title');
                    toast.$toast(msg, title, {
                        channel: 'success',
                    });
                }).catch(e => {
                    state.errors[id] = getErrorMessages(e);
                    throw e;
                });

            };
            const getErrors = (id, field) => {
                let apiErrors = [];
                if(!!state.errors[id] && !!state.errors[id][field]) {
                    apiErrors = state.errors[id][field];
                }
                return [
                    ...v.fields[id][field].errors,
                    ...apiErrors,
                ];
            };
            const showAddRoleModal = _ => {
                if(!can('add_edit_role')) return;
                showAddRole();
            };
            const deleteRole = rid => {
                if(!can('delete_role')) return;
                showDeleteRole(getRoleBy(rid));
            };
            const anyRoleDirty = _ => {
                let isDirty = false;
                for(let i=0; i<state.roleList.length; i++) {
                    const r = state.roleList[i];
                    if(roleDirty(r.id)) {
                        isDirty = true;
                        break;
                    }
                }
                return isDirty;
            };
            // Used in Discard Modal to make all fields undirty
            const resetData = _ => {
                for(let i=0; i<state.roleList.length; i++) {
                    resetRole(state.roleList[i].id);
                }
            };
            // Used in Discard Modal to store data before moving on
            const onBeforeConfirm = async _ => {
                for(let i=0; i<state.roleList.length; i++) {
                    const rid = state.roleList[i].id;
                    if(
                        (
                            !v.fields[rid].display_name.meta.dirty ||
                            (
                                v.fields[rid].display_name.meta.dirty &&
                                v.fields[rid].display_name.meta.valid
                            )
                        ) &&
                        (
                            !v.fields[rid].description.meta.dirty ||
                            (
                                v.fields[rid].description.meta.dirty &&
                                v.fields[rid].description.meta.valid
                            )
                        ) &&
                        (
                            !v.fields[rid].permissions.meta.dirty ||
                            (
                                v.fields[rid].permissions.meta.dirty &&
                                v.fields[rid].permissions.meta.valid
                            )
                        )
                    ) {
                        await patchRole(rid);
                    }
                }
            };

            // DATA
            const state = reactive({
                roleList: computed(_ => store.getters.roles()),
                permissions: computed(_ => store.getters.permissions),
                dataInitialized: computed(_ => state.roleList.length > 0 && state.permissions.length > 0),
                errors: {},
            });
            const v = reactive({
                fields: computed(_ => {
                    const list = {};
                    for(let i=0; i<state.roleList.length; i++) {
                        const r = state.roleList[i];
                        const {
                            errors: edn,
                            meta: mdn,
                            value: vdn,
                            handleInput: hidn,
                            resetField: hrdn,
                        } = useField(`displayname_${r.id}`, yup.string().required().max(255), {
                            initialValue: r.display_name,
                        });
                        const {
                            errors: edesc,
                            meta: mdesc,
                            value: vdesc,
                            handleInput: hidesc,
                            resetField: hrdesc,
                        } = useField(`description_${r.id}`, yup.string().required().max(255), {
                            initialValue: r.description,
                        });
                        const {
                            errors: ep,
                            meta: mp,
                            value: vp,
                            handleInput: hip,
                            resetField: hrp,
                        } = useField(`permissions_${r.id}`, yup.array(), {
                            initialValue: r.permissions,
                        });
                        list[r.id] = reactive({
                            display_name: {
                                errors: edn,
                                meta: mdn,
                                value: vdn,
                                handleInput: hidn,
                                reset: hrdn,
                            },
                            description: {
                                errors: edesc,
                                meta: mdesc,
                                value: vdesc,
                                handleInput: hidesc,
                                reset: hrdesc,
                            },
                            permissions: {
                                errors: ep,
                                meta: mp,
                                value: vp,
                                handleInput: hip,
                                reset: hrp,
                            },
                        });
                    }
                    return list;
                })
            });

            // ON BEFORE LEAVE
            onBeforeRouteLeave(async (to, from) => {
                if(anyRoleDirty()) {
                    showDiscard(to, resetData, onBeforeConfirm);
                    return false;
                } else {
                    return true;
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                date,
                getClassByValidation,
                // LOCAL
                roleDirty,
                roleValid,
                resetRole,
                patchRole,
                deleteRole,
                getErrors,
                showAddRoleModal,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    }
</script>
