<template>
    <div class="form-check form-switch h-100 d-flex align-items-center">
        <input
            :id="name"
            v-model="v.value"
            class="form-check-input"
            type="checkbox"
            :disabled="disabled"
            :name="name"
            @input="v.handleInput"
        >
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

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
        },
        emits: ['change'],
        setup(props, context) {
            const {
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: initValue,
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            // DATA
            const initValue = !!value.value ? true : false;
            const {
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`perc_${name.value}`, yup.boolean(), {
                type: 'checkbox',
                valueProp: initValue,
                initialValue: initValue,
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                handleInput,
                meta,
                resetField,
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
                // HELPERS
                // LOCAL
                resetFieldState,
                undirtyField,
                // STATE
                state,
                v,
            }
        },
    }
</script>
