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
            'edit',
            'require',
            'remove'
        ],
        props: {
            attribute: {
                type: Object,
                required: true,
            },
        },
        setup(props, { emit }) {
            const { t } = useI18n();

            const getRequiredButtonClass = computed(() => {
                if(props.attribute?.pivot?.metadata?.required) {
                    return 'btn-primary';
                } else {
                    return 'btn-outline-secondary';
                }
            });

            const buttons = computed(() => {
                const baseIconClass = 'fas fa-xs ';
                return [
                    {
                        icon: baseIconClass + 'fa-sort',
                        title: t('global.resort'),
                        action: () => {
                            // No action needed, just a handle
                        },
                    },
                    {
                        icon: baseIconClass + 'fa-edit',
                        title: t('global.edit'),
                        action: () => {
                            emit('edit');
                        },
                    },
                    {
                        icon: 'fas fa-xs fa-asterisk',
                        title: t('global.required'),
                        classes: getRequiredButtonClass.value,
                        action: () => {
                            emit('require');
                        },
                    },
                    {
                        icon: baseIconClass + 'fa-times',
                        title: t('global.remove'),
                        classes: 'btn-outline-danger',
                        action: () => {
                            emit('remove');
                        },
                    },
                ];
            });

            return {
                t,
                buttons,
            };
        },

    };

</script>