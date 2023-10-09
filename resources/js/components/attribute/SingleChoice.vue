<template>
    <multiselect
        :classes="multiselectResetClasslist"
        :valueProp="'id'"
        :label="'concept_url'"
        :track-by="'concept_url'"
        :object="true"
        :mode="'single'"
        :disabled="disabled"
        :options="selections"
        :name="name"
        :placeholder="t('global.select.placeholder')"
        v-model="v.value"
        @change="value => v.handleChange(value)">
        <template v-slot:option="{ option }">
            {{ translateConcept(option.concept_url) }}
        </template>
        <template v-slot:singlelabel="{ value }">
            <div class="multiselect-single-label">
                {{ translateConcept(value.concept_url) }}
            </div>
        </template>
    </multiselect>
</template>

<script>
    import {
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

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
                required: true,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                disabled,
                value,
                selections,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`sc_${name.value}`, yup.mixed(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });


            watch(value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                multiselectResetClasslist,
                // LOCAL
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                disabled,
                selections,
                // STATE
                state,
                v,
            }
        },
    }
</script>
