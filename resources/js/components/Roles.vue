<template>
    <div
        v-if="state.setupFinished"
        class="d-flex flex-column h-100"
    >
        <h4 class="d-flex flex-row gap-2 align-items-center">
            {{ t('global.roles') }}
            <button
                type="button"
                class="btn btn-outline-success btn-sm"
                :disabled="!can('users_roles_create')"
                @click="showAddRoleModal()"
            >
                <i class="fas fa-fw fa-plus" /> {{ t('main.role.add_button') }}
            </button>
        </h4>
        <div class="table-responsive flex-grow-1">
            <table
                v-if="state.dataInitialized"
                class="table table-striped table-hover table-light mb-0"
            >
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
                    <tr
                        v-for="role in state.roleList"
                        :key="role.id"
                    >
                        <td>
                            {{ role.name }}
                        </td>
                        <td v-if="v.fields[role.id]">
                            <input
                                v-model="v.fields[role.id].display_name.value"
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(role.id, 'display_name'))"
                                :name="`displayname_${role.id}`"
                                @input="e => v.fields[role.id].display_name.handleChange(e)"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in getErrors(role.id, 'display_name')"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td v-if="v.fields[role.id]">
                            <input
                                v-model="v.fields[role.id].description.value"
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(role.id, 'description'))"
                                :name="`description_${role.id}`"
                                @input="e => v.fields[role.id].description.handleChange(e)"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in getErrors(role.id, 'description')"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td v-if="v.fields[role.id]">
                            <div class="d-flex flex-row gap-2">
                                <div
                                    v-if="role.derived"
                                    class="d-flex flex-row gap-2 align-items-center"
                                >
                                    <span class="text-muted">
                                        {{ t('main.role.preset.derived_from') }}
                                    </span>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-fw fa-shield-alt" />
                                        {{ t(`main.role.preset.${role.derived.name}`) }}
                                    </span>
                                </div>
                                <a
                                    v-if="can('users_roles_write')"
                                    href="#"
                                    class="text-decoration-none text-info"
                                    :title="t('global.edit')"
                                    @click.prevent="openAccessControlModal(role.id)"
                                >
                                    <i class="fas fa-fw fa-edit" />
                                </a>
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
                                    <i class="fas fa-fw fa-ellipsis-vertical"></i>
                                    <sup class="notification-info" v-if="roleDirty(role.id)">
                                        <i class="fas fa-fw fa-xs fa-circle text-warning"></i>
                                    </sup>
                                </span>
                                <div
                                    class="dropdown-menu"
                                    :aria-labelledby="`role-options-dropdown-${role.id}`"
                                >
                                    <a
                                        v-if="roleDirty(role.id)"
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('users_roles_write')"
                                        @click.prevent="patchRole(role.id)"
                                    >
                                        <i class="fas fa-fw fa-check text-success" /> {{ t('global.save') }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('users_roles_delete')"
                                        @click.prevent="deleteRole(role.id)"
                                    >
                                        <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
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
        onMounted,
        reactive,
        watch,
    } from 'vue';

    import { onBeforeRouteLeave } from 'vue-router';
    import { useI18n } from 'vue-i18n';
    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import useUserStore from '@/bootstrap/stores/user.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        showDiscard,
        showAddRole,
        showDeleteRole,
        showAccessControlModal,
    } from '@/helpers/modal.js';
    import {
        can,
        getClassByValidation,
        getErrorMessages,
    } from '@/helpers/helpers.js';
    import {
        date,
    } from '@/helpers/filters.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const userStore = useUserStore();
            const toast = useToast();

            // FUNCTIONS
            const updateValidationState = roles => {
                const currentIds = roles.map(r => r.id);
                const oldIds = Object.keys(v.fields);

                for(let i=0; i<oldIds.length; i++) {
                    const oid = oldIds[i];

                    // Delete validation rules if role is deleted
                    if(!currentIds.includes(oid) && !!v.fields[oid]) {
                        delete v.fields[oid];
                    }
                }

                for(let i=0; i<roles.length; i++) {
                    const r = roles[i];
                    // do not initialize existing roles
                    if(!!v.fields[r.id]) continue;

                    const {
                        errors: edn,
                        meta: mdn,
                        value: vdn,
                        handleChange: hidn,
                        resetField: hrdn,
                    } = useField(`displayname_${r.id}`, yup.string().required().max(255), {
                        initialValue: r.display_name,
                    });
                    const {
                        errors: edesc,
                        meta: mdesc,
                        value: vdesc,
                        handleChange: hidesc,
                        resetField: hrdesc,
                    } = useField(`description_${r.id}`, yup.string().required().max(255), {
                        initialValue: r.description,
                    });
                    v.fields[r.id] = reactive({
                        display_name: {
                            errors: edn,
                            meta: mdn,
                            value: vdn,
                            handleChange: hidn,
                            reset: hrdn,
                        },
                        description: {
                            errors: edesc,
                            meta: mdesc,
                            value: vdesc,
                            handleChange: hidesc,
                            reset: hrdesc,
                        },
                    });
                }
            };
            const roleDirty = id => {
                if(!v.fields[id]) return false;

                return v.fields[id].display_name.meta.dirty ||
                    v.fields[id].description.meta.dirty;
            };
            const roleValid = id => {
                if(!v.fields[id]) return false;

                return v.fields[id].display_name.meta.valid ||
                    v.fields[id].description.meta.valid;
            };
            const resetRole = id => {
                v.fields[id].display_name.reset();
                v.fields[id].description.reset();
            };
            const resetRoleMeta = id => {
                v.fields[id].display_name.reset({
                    value: v.fields[id].display_name.value,
                });
                v.fields[id].description.reset({
                    value: v.fields[id].description.value,
                });
            };
            const patchRole = async id => {
                if(!roleDirty(id) || !roleValid(id) || !can('users_roles_write')) {
                    return;
                }

                const data = {};
                if(v.fields[id].display_name.meta.dirty) {
                    data.display_name = v.fields[id].display_name.value;
                }
                if(v.fields[id].description.meta.dirty) {
                    data.description = v.fields[id].description.value;
                }

                return await userStore.updateRole(id, data).then(data => {
                    state.errors[id] = {};
                    resetRoleMeta(id);
                    const msg = t('main.role.toasts.updated.msg', {
                        name: data.display_name
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
                const formErrors = v.fields[id] ? v.fields[id][field].errors : [];
                return [
                    ...formErrors,
                    ...apiErrors,
                ];
            };
            const showAddRoleModal = _ => {
                if(!can('users_roles_create')) return;
                showAddRole();
            };
            const deleteRole = rid => {
                if(!can('users_roles_delete')) return;
                showDeleteRole(userStore.getRoleBy(rid));
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
            const openAccessControlModal = roleId => {
                if(!can('users_roles_write')) return;

                showAccessControlModal(roleId);
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
                        )
                    ) {
                        await patchRole(rid);
                    }
                }
            };

            // DATA
            const state = reactive({
                setupFinished: false,
                roleList: computed(_ => userStore.getRoles()),
                dataInitialized: computed(_ => state.roleList.length > 0),
                errors: {},
            });
            const v = reactive({
                fields: {},
            });

            // ON MOUNTED
            onMounted(_ => {
                updateValidationState(state.roleList);
                state.setupFinished = true;
            })

            // WATCHER
            watch(_ => state.roleList.length, _ => {
                updateValidationState(state.roleList);
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
                openAccessControlModal,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    }
</script>
