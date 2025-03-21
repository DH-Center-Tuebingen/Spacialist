<template>
    <tr>
        <td class="text-start">
            {{ label }}
        </td>
        <td
            v-for="right in rights"
            :key="`permission-row-${label}-${right}`"
        >
            <a
                href="#"
                class="text-reset text-decoration-none"
                draggable="false"
                @click.prevent="change(right)"
            >
                <AccessControlState :status="permissionMap?.[name]?.[right] ?? Infinity" />
            </a>
        </td>
        <td>
            <a
                href="#"
                class="text-reset text-decoration-none"
                draggable="false"
                @click.prevent="change('all')"
            >
                <AccessControlState :status="allState(name)" />
            </a>
        </td>
    </tr>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    import { determineUniformState } from '@/helpers/role.js';

    import AccessControlState from '@/components/role/AccessControlState.vue';

    export default {
        components: {
            AccessControlState,
        },
        props: {
            name: {
                required: true,
                type: String,
            },
            label: {
                required: true,
                type: String,
            },
            permissionMap: {
                required: true,
                type: Object,
            },
            rights: {
                required: true,
                type: Array,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();

            const allState = name => {
                return determineUniformState(props.permissionMap[name], props.rights);
            };

            const change = right => {
                context.emit('change', props.name, right);
            };

            return {
                t,
                // LOCAL
                allState,
                change,
            };
        }
    };

</script>