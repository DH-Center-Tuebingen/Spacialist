<template>
    <input
        :id="name"
        v-model="v.value"
        class="form-control"
        type="text"
        :disabled="disabled"
        :name="name"
        @input="v.handleInput"
    >
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
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`str_${name.value}`, yup.string(), {
                initialValue: value.value,
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
