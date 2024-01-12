<template>
    <div
        v-if="state.setupFinished"
        class="d-flex flex-column h-100"
    >
        <h4 class="d-flex flex-row gap-2 align-items-center">
            {{ t('global.groups') }}
            <button
                type="button"
                class="btn btn-outline-success btn-sm"
                :disabled="!can('users_roles_create')"
                @click="showAddGroupModal()"
            >
                <i class="fas fa-fw fa-plus" /> {{ t('main.group.add_button') }}
            </button>
        </h4>
        <div class="table-responsive flex-grow-1">
            <table
                v-if="state.dataInitialized"
                class="table table-striped table-hover table-light align-middle mb-0"
            >
                <thead class="sticky-top">
                    <tr>
                        <th>{{ t('global.name') }}</th>
                        <th>{{ t('global.display_name') }}</th>
                        <th>{{ t('global.description') }}</th>
                        <th>{{ t('global.created_at') }}</th>
                        <th>{{ t('global.updated_at') }}</th>
                        <th>{{ t('global.options') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="grp in state.groupList"
                        :key="grp.id"
                    >
                        <td>
                            {{ grp.name }}
                        </td>
                        <td v-if="v.fields[grp.id]">
                            <input
                                v-model="v.fields[grp.id].display_name.value"
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(grp.id, 'display_name'))"
                                :name="`displayname_${grp.id}`"
                                @input="e => v.fields[grp.id].display_name.handleChange(e)"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in getErrors(grp.id, 'display_name')"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td v-if="v.fields[grp.id]">
                            <input
                                v-model="v.fields[grp.id].description.value"
                                type="text"
                                class="form-control"
                                required
                                :class="getClassByValidation(getErrors(grp.id, 'description'))"
                                :name="`description_${grp.id}`"
                                @input="e => v.fields[grp.id].description.handleChange(e)"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in getErrors(grp.id, 'description')"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span :title="date(grp.created_at)">
                                {{ ago(grp.created_at) }}
                            </span>
                        </td>
                        <td>
                            <span :title="date(grp.updated_at)">
                                {{ ago(grp.updated_at) }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <span
                                    :id="`role-options-dropdown-${grp.id}`"
                                    class="clickable"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-fw fa-ellipsis-vertical" />
                                    <sup
                                        v-if="groupDirty(grp.id)"
                                        class="notification-info"
                                    >
                                        <i class="fas fa-fw fa-xs fa-circle text-warning" />
                                    </sup>
                                </span>
                                <div
                                    class="dropdown-menu"
                                    :aria-labelledby="`role-options-dropdown-${grp.id}`"
                                > 
                                    <a
                                        v-if="groupDirty(grp.id)"
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('users_roles_write')"
                                        @click.prevent="patchGroup(grp.id)"
                                    >
                                        <i class="fas fa-fw fa-check text-success" /> {{ t('global.save') }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('users_roles_delete')"
                                        @click.prevent="deleteGroup(grp.id)"
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

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        showDiscard,
        showAddGroup,
        showDeleteGroup,
    } from '@/helpers/modal.js';
    import {
        can,
        getClassByValidation,
        getErrorMessages,
    } from '@/helpers/helpers.js';
    import {
        ago,
        date,
    } from '@/helpers/filters.js';
    import {
        patchGroupData,
    } from '@/api.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const toast = useToast();

            // FUNCTIONS
            const updateValidationState = groups => {
                const currentIds = groups.map(r => r.id);
                const oldIds = Object.keys(v.fields);

                for(let i=0; i<oldIds.length; i++) {
                    const oid = oldIds[i];

                    // Delete validation rules if role is deleted
                    if(!currentIds.includes(oid) && !!v.fields[oid]) {
                        delete v.fields[oid];
                    }
                }

                for(let i=0; i<groups.length; i++) {
                    const g = groups[i];
                    // do not initialize existing groups
                    if(!!v.fields[g.id]) continue;

                    const {
                        errors: edn,
                        meta: mdn,
                        value: vdn,
                        handleChange: hidn,
                        resetField: hrdn,
                    } = useField(`displayname_${g.id}`, yup.string().required().max(255), {
                        initialValue: g.display_name,
                    });
                    const {
                        errors: edesc,
                        meta: mdesc,
                        value: vdesc,
                        handleChange: hidesc,
                        resetField: hrdesc,
                    } = useField(`description_${g.id}`, yup.string().required().max(255), {
                        initialValue: g.description,
                    });
                    v.fields[g.id] = reactive({
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
            const groupDirty = id => {
                if(!v.fields[id]) return false;

                return v.fields[id].display_name.meta.dirty ||
                    v.fields[id].description.meta.dirty;
            };
            const groupValid = id => {
                if(!v.fields[id]) return false;
                
                return v.fields[id].display_name.meta.valid ||
                    v.fields[id].description.meta.valid;
            };
            const resetGroup = id => {
                v.fields[id].display_name.reset();
                v.fields[id].description.reset();
            };
            const resetGroupMeta = id => {
                v.fields[id].display_name.reset({
                    value: v.fields[id].display_name.value,
                });
                v.fields[id].description.reset({
                    value: v.fields[id].description.value,
                });
            };
            const patchGroup = async id => {
                if(!groupDirty(id) || !groupValid(id) || !can('users_roles_write')) {
                    return;
                }

                const data = {};
                if(v.fields[id].display_name.meta.dirty) {
                    data.display_name = v.fields[id].display_name.value;
                }
                if(v.fields[id].description.meta.dirty) {
                    data.description = v.fields[id].description.value;
                }

                return await patchGroupData(id, data).then(data => {
                    state.errors[id] = {};
                    resetGroupMeta(id);
                    store.dispatch('updateGroup', {
                        id: data.id,
                        display_name: data.display_name,
                        description: data.description,
                        updated_at: data.updated_at,
                    });
                    const group = getGroupBy(id);
                    const msg = t('main.group.toasts.updated.msg', {
                        name: group.display_name
                    });
                    const title = t('main.group.toasts.updated.title');
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
            const showAddGroupModal = _ => {
                if(!can('users_roles_create')) return;
                showAddGroup();
            };
            const deleteGroup = gid => {
                if(!can('users_roles_delete')) return;
                showDeleteGroup(getGroupBy(gid));
            };
            const anyGroupDirty = _ => {
                let isDirty = false;
                for(let i=0; i<state.groupList.length; i++) {
                    const g = state.groupList[i];
                    if(groupDirty(g.id)) {
                        isDirty = true;
                        break;
                    }
                }
                return isDirty;
            };
            // Used in Discard Modal to make all fields undirty
            const resetData = _ => {
                for(let i=0; i<state.groupList.length; i++) {
                    resetGroup(state.groupList[i].id);
                }
            };
            // Used in Discard Modal to store data before moving on
            const onBeforeConfirm = async _ => {
                for(let i=0; i<state.groupList.length; i++) {
                    const gid = state.groupList[i].id;
                    if(
                        (
                            !v.fields[gid].display_name.meta.dirty ||
                            (
                                v.fields[gid].display_name.meta.dirty &&
                                v.fields[gid].display_name.meta.valid
                            )
                        ) &&
                        (
                            !v.fields[gid].description.meta.dirty ||
                            (
                                v.fields[gid].description.meta.dirty &&
                                v.fields[gid].description.meta.valid
                            )
                        )
                    ) {
                        await patchGroup(gid);
                    }
                }
            };

            // DATA
            const state = reactive({
                setupFinished: false,
                groupList: computed(_ => store.getters.groups),
                dataInitialized: computed(_ => state.groupList.length > 0),
                errors: {},
            });
            const v = reactive({
                fields: {},
            });

            // ON MOUNTED
            onMounted(_ => {
                updateValidationState(state.groupList);
                state.setupFinished = true;
            })

            // WATCHER
            watch(_ => state.groupList.length, _ => {
                updateValidationState(state.groupList);
            });

            // ON BEFORE LEAVE
            onBeforeRouteLeave(async (to, from) => {
                if(anyGroupDirty()) {
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
                ago,
                date,
                getClassByValidation,
                // LOCAL
                groupDirty,
                groupValid,
                resetGroup,
                patchGroup,
                deleteGroup,
                getErrors,
                showAddGroupModal,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    }
</script>
