<template>
    <tr>
        <td class="text-start">
            {{ label }}
        </td>
        <td
            v-for="right in rights"
            :key="`${i}-${right}`"
        >
            <a
                href="#"
                class="text-reset text-decoration-none"
                @click.prevent="change(right)"
            >
                <AccessControlState :status="permissionMap?.[name]?.[right] ?? Infinity" />
            </a>
        </td>
        <td>
            <a
                href="#"
                class="text-reset text-decoration-none"
                @click.prevent="change('all')"
            >
                <AccessControlState :status="allState(name)" />
            </a>
        </td>
    </tr>
</template>

<script>

    import { useI18n } from 'vue-i18n';

    import AccessControlState from '@/components/role/AccessControlState.vue';
    import { determineUniformState } from '../../helpers/role';

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

            const allState = (name) => {
                return determineUniformState(props.permissionMap[name], props.rights);
            };

            const change = (right) => {
                context.emit('change', props.name, right);
            };

            return {
                allState,
                change,
                t: useI18n().t,
            };
        }
    };

</script>