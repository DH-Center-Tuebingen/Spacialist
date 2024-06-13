<template>
    <table class="table table-small table-hover table-striped text-center">
        <thead class="text-muted">
            <tr>
                <th />
                <th
                    v-for="right in rights"
                    :key="`header-${right}`"
                    class="fw-normal"
                >
                    {{ t(`main.role.permissions.types.${right}`) }}
                </th>
                <th class="fw-normal">
                    {{ t('global.select_all') }}
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="fw-bold text-start py-0">
                    {{ t(`main.role.permissions.module.core`) }}
                </th>
                <th
                    v-for="right in rights"
                    :key="`module-core-${right}`"
                />
                <th />
            </tr>

            <PermissionRow
                v-for="permissionGroup in permissionGroups.core"
                :key="permissionGroup"
                :label="t(`main.role.permissions.groups.${permissionGroup}`)"
                :name="permissionGroup"
                :permission-map="permissionMap"
                :rights="rights"
                @change="(grp, right) => changePermissionState(grp, right)"
            />

            <template
                v-for="(pluginGroup, pluginName) in pluginsUsingPermissions"
                :key="pluginName"
            >
                <tr>
                    <td
                        colspan="7"
                        class="fw-bold text-start"
                    >
                        {{ t(`plugin.${pluginName}.title`) }} ({{ t(`main.role.permissions.groups.is_plugin`) }})
                    </td>
                </tr>
                <PermissionRow
                    v-for="permissionGroup in pluginGroup"
                    :key="permissionGroup"
                    :label="t(`main.role.permissions.groups.${permissionGroup}`)"
                    :permission-map="permissionMap"
                    :name="permissionGroup"
                    :rights="rights"
                    @change="(grp, right) => changePermissionState(grp, right)"
                >
                    />
                </permissionrow>
            </template>
        </tbody>
    </table>
</template>


<script>
    import { useI18n } from 'vue-i18n';
    import { cloneDeep } from 'lodash';
    import PermissionRow from './PermissionRow.vue';
    import { determineUniformState } from '../../helpers/role';
    import { computed } from 'vue';

    export default {
        components: {
            PermissionRow
        },
        props: {
            permissionGroups: {
                type: Object,
                required: true,
                validator: (value) => {
                    return Object.hasOwnProperty(value, 'core');
                },
            },
            permissionMap: {
                required: true,
                type: Object
            },
            rights: {
                required: false,
                type: Array,
                default: () => {
                    return ['read', 'write', 'create', 'delete', 'share'];
                },
            },
            disabled: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['update:permissionMap'],
        setup(props, ctx) {

            const t = useI18n().t;


            const changePermissionState = (permissionGroup, right) => {
                if(props.disabled) {
                    return;
                }

                const newPermissionMap = cloneDeep(props.permissionMap);

                if(right == 'all') {

                    let combinedState = determineUniformState(newPermissionMap[permissionGroup], props.rights);
                    console.log('newState', combinedState);

                    const newState = (combinedState + 1) % 2;
                    for(let k in newPermissionMap[permissionGroup]) {
                        newPermissionMap[permissionGroup][k] = newState;
                    }
                } else {
                    newPermissionMap[permissionGroup][right] = (newPermissionMap[permissionGroup][right] + 1) % 2;
                }
                ctx.emit('update:permissionMap', newPermissionMap);
            };

            const pluginsUsingPermissions = computed(() => {
                const entries = Object.entries(props.permissionGroups.plugins);

                let filteredEntries = entries.filter(([_, plginPermissions]) => {
                    if(Array.isArray(plginPermissions)) {
                        return plginPermissions.length > 0;
                    }
                    return false;
                });

                const filteredPluginGroup = filteredEntries.reduce((acc, [pluginName, pluginPermissions]) => {
                    acc[pluginName] = pluginPermissions;
                    return acc;
                }, {});
                return filteredPluginGroup;
            });

            return {
                changePermissionState,
                controlClasses: {
                    'opacity-50': props.disabled,
                },
                pluginsUsingPermissions,
                t,
            };
        },
    };

</script>