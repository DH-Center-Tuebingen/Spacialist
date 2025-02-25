<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="access-control-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.role.permissions.access_control_title') }}
                    <small>
                        {{ state.role.display_name }}
                    </small>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <div class="d-flex flex-row-reverse justify-content-between gap-2">
                    <div>
                        <div class="form-check form-switch mb-0">
                            <input
                                :id="`role-${state.role.id}-moderation`"
                                v-model="state.moderated"
                                class="form-check-input"
                                type="checkbox"
                                role="switch"
                            >
                            <label
                                class="form-check-label"
                                :for="`role-${state.role.id}-moderation`"
                            >
                                {{ t('main.role.permissions.moderation.required') }}
                            </label>
                        </div>
                    </div>
                    <div
                        v-if="state.isDerived"
                        class="d-flex flex-row align-items-center gap-2"
                    >
                        <span class="text-muted">
                            {{ t('main.role.preset.derived_from') }}
                        </span>
                        <span class="badge bg-primary">
                            <i class="fas fa-fw fa-shield-alt" />
                            {{ t(`main.role.preset.${state.role.derived.name}`) }}
                            <a
                                v-if="state.differsFromPreset"
                                href="#"
                                class="text-decoration-none text-reset"
                                :title="`Reset role to preset`"
                                @click.prevent="resetToPreset()"
                            >
                                |
                                <i class="fas fa-fw fa-undo" />
                            </a>
                        </span>
                    </div>
                </div>
                <PermissionMatrix
                    v-if="state.permissionsLoaded"
                    v-model:permission-map="state.permissionStates"
                    :permission-groups="state.permissionGroups"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    @click="savePermissions()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useUserStore from '@/bootstrap/stores/user.js';

    import PermissionMatrix from '@/components/user/PermissionMatrix.vue';

    import {
        getAccessGroups,
    } from '@/helpers/accesscontrol.js';

    export default {
        components: {
            PermissionMatrix,
        },
        props: {
            roleId: {
                type: Number,
                required: true,
            }
        },
        emits: ['save', 'cancel'],
        setup(props, context) {
            const { t } = useI18n();
            const userStore = useUserStore();
            const {
                roleId,
            } = toRefs(props);

            // FUNCTIONS
            const loadRolePermissions = permissions => {
                if(permissions.length === 0) {
                    state.permissionGroups.core.forEach(pg => {
                        state.permissionStates[pg] = {
                            read: 0,
                            write: 0,
                            create: 0,
                            delete: 0,
                            share: 0,
                        };
                    });
                    for(let k in state.permissionGroups.plugins) {
                        const curr = state.permissionGroups.plugins[k];
                        curr.forEach(pg => {
                            state.permissionStates[pg] = {
                                read: 0,
                                write: 0,
                                create: 0,
                                delete: 0,
                                share: 0,
                            };
                        });
                    }
                    return;
                }

                // simple check whether this is an array of objects or strings
                const isObject = (typeof permissions[0]) != 'string';
                // we only need names
                const givenPermNames = isObject ? permissions.map(p => p.name) : permissions;

                state.permissionGroups.core.forEach(pg => {
                    state.permissionStates[pg] = {
                        read: givenPermNames.some(p => p == `${pg}_read`) ? 1 : 0,
                        write: givenPermNames.some(p => p == `${pg}_write`) ? 1 : 0,
                        create: givenPermNames.some(p => p == `${pg}_create`) ? 1 : 0,
                        delete: givenPermNames.some(p => p == `${pg}_delete`) ? 1 : 0,
                        share: givenPermNames.some(p => p == `${pg}_share`) ? 1 : 0,
                    };
                });
                for(let k in state.permissionGroups.plugins) {
                    const curr = state.permissionGroups.plugins[k];
                    curr.forEach(pg => {
                        state.permissionStates[pg] = {
                            read: givenPermNames.some(p => p == `${pg}_read`) ? 1 : 0,
                            write: givenPermNames.some(p => p == `${pg}_write`) ? 1 : 0,
                            create: givenPermNames.some(p => p == `${pg}_create`) ? 1 : 0,
                            delete: givenPermNames.some(p => p == `${pg}_delete`) ? 1 : 0,
                            share: givenPermNames.some(p => p == `${pg}_share`) ? 1 : 0,
                        };
                    });
                }
            };
            const allState = group => {
                let stateCnt = 0;
                for(let k in state.permissionStates[group]) {
                    if(state.permissionStates[group][k] === 1) {
                        stateCnt++;
                    }
                }

                if(stateCnt == 0) {
                    return 0;
                } else if(stateCnt == Object.keys(state.permissionStates[group]).length) {
                    return 1;
                } else {
                    return 2;
                }
            };
            const resetToPreset = _ => {
                if(!state.isDerived) return;

                const preset = userStore.getRolePreset(state.role.derived.name);

                if(!preset) return;

                loadRolePermissions(preset.fullSet);
            };
            const closeModal = _ => {
                context.emit('cancel', false);
            };
            const savePermissions = _ => {
                const permissions = [];
                for(let k in state.permissionStates) {
                    const set = state.permissionStates[k];

                    for(let l in set) {
                        const state = set[l];
                        if(state == 1) {
                            permissions.push(`${k}_${l}`);
                        }
                    }
                }
                context.emit('save', {
                    permissions: permissions,
                    is_moderated: !!state.moderated,
                });
            };

            // DATA
            const state = reactive({
                permissionsLoaded: false,
                role: computed(_ => userStore.getRoleBy(roleId.value, 'id', true) || {}),
                permissionStates: {},
                permissionGroups: null,
                moderated: false,
                isDerived: computed(_ => state.role.derived),
                differsFromPreset: computed(_ => {
                    if(!state.isDerived) return false;

                    // state.role.derived.name
                    const preset = userStore.getRolePreset(state.role.derived.name);
                    if(!preset) return false;

                    const presetPerms = preset.fullSet;
                    presetPerms.sort();
                    const rolePerms = state.role.permissions.map(p => p.name);
                    rolePerms.sort();

                    return presetPerms.length != rolePerms.length || presetPerms.join() != rolePerms.join();
                }),
            });

            // ON MOUNTED
            onMounted(async _ => {
                state.moderated = state.role.is_moderated;
                state.permissionGroups = await getAccessGroups();
                loadRolePermissions(state.role.permissions);
                state.permissionsLoaded = true;
            });

            // WATCHER
            watch(_ => state.role.permissions, (newValue) => {
                loadRolePermissions(state.role.permissions);
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                allState,
                resetToPreset,
                closeModal,
                savePermissions,
                // STATE
                state,
            };
        },
    };
</script>
