<template>
    <multiselect
        :valueProp="'id'"
        :label="'concept_url'"
        :track-by="'concept_url'"
        :object="true"
        :mode="'single'"
        :disabled="disabled"
        :options="selections"
        :name="name"
        :placeholder="t('global.select.placeholder')"
        v-model="value">
        <template v-slot:option="{ option }">
            {{ translateConcept(option.concept_url) }}
        </template>
        <template v-slot:singlelabel="{ value }">
            <div class="px-2">
                {{ translateConcept(value.concept_url) }}
            </div>
        </template>
    </multiselect>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '../../helpers/helpers.js';

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
                type: Object,
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
                translateConcept,
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
