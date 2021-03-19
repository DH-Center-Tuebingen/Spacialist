<template>
    <multiselect
        :valueProp="'id'"
        :label="'thesaurus_url'"
        :track-by="'thesaurus_url'"
        :mode="'tags'"
        :disabled="disabled"
        :options="selections"
        :name="name"
        :placeholder="t('global.select.placeholder')"
        v-model="value">
    </multiselect>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Number,
                required: true,
            },
            selections: {
                type: Array,
                required: false,
                default: [],
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({

            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                // PROPS
                name,
                disabled,
                value,
                // STATE
                state,
            }
        },
    }
</script>
