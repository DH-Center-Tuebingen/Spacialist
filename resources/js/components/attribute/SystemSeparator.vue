<template>
    <div class="d-flex flex-row align-items-center gap-2">
        <div
            class="bg-secondary flex-grow-1 rounded-5"
            style="height: 3px;"
        />
        <span class="fw-bold">
            {{ translateConcept(state.title) }}
        </span>
        <div
            class="bg-secondary flex-grow-1 rounded-5"
            style="height: 3px;"
        />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            title: {
                type: String,
                required: false,
                default: null,
            },
        },
        setup(props, context) {
            const {
                title,
            } = toRefs(props);
            const { t } = useI18n();
            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                title: computed(_ => title.value ? title.value : t('global.attributes.system-separator')),
            });

            // RETURN
            return {
                // HELPERS
                translateConcept,
                // LOCAL
                // PROPS
                // STATE
                state,
            };
        },
    }
</script>
