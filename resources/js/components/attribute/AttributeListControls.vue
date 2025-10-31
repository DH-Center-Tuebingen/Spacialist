<template>
    <FabButtonList
        class="position-absolute start-0 top-0"
        :buttons="buttons"
    />
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    import FabButtonList from '../forms/button/FabButtonList.vue';

    export default {
        components: {
            FabButtonList,
        },
        emits: [
            'delete',
        ],
        props: {
            attribute: {
                type: Object,
                required: true,
            },
        },
        setup(props, { emit }) {
            const { t } = useI18n();

            const buttons = computed(() => {
                return [
                {
                    icon: 'fas fa-fw fa-xs fa-trash',
                    title: t('global.delete'),
                    classes: 'btn-outline-danger',
                    action: () => {
                        console.log("EMIT DELETE");
                        emit('delete');
                    },
                },
            ]});

            return {
                t,
                buttons,
            };
        },

    };

</script>