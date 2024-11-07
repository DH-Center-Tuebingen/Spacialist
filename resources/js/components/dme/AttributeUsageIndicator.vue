<template>
    <span
        class="rounded-circle align-self-center"
        :class="state.colorIndicator"
        :style="`width: ${size}px; height: ${size}px;`"
        :title="t('main.datamodel.attribute.indicator_info', {cnt: count}, count)"
    />
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            count: {
                required: true,
                type: Number,
            },
            size: {
                required: false,
                type: Number,
                default: 8,
            },
        },
        setup(props, context) {
            const { t } = useI18n();

            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                positive: computed(_ => props.count > 0),
                colorIndicator: computed(_ => state.positive ? 'bg-success' : 'bg-danger'),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                // STATE
                state,
            };
        },
    };
</script>