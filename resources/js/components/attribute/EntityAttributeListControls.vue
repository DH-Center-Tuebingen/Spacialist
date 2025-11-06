<template>
    <FabButtonList
        class="position-absolute start-0 top-0 z-1"
        :buttons="buttons"
    />
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    import { FabButtonList } from 'dhc-components';

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

            const active = computed(() => {
                return props.attribute?.pivot?.metadata?.required ?? false;
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
                        color: active.value ? 'primary' : 'secondary',
                        active: active.value,
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