<template>
    <input
        class="form-control"
        type="number"
        step="1"
        placeholder="0"
        :disabled="disabled"
        :id="name"
        :name="name"
        v-model.number="v.value"
        @input="v.handleInput" />
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
            } = useField(`int_${name.value}`, yup.number().integer(), {
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
                // HELPERS
                // LOCAL
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                disabled,
                value,
                // STATE
                state,
                v,
            }
        },
    }
</script>
