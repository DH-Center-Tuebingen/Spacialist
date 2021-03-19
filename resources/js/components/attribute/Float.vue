<template>
    <input
        class="form-control"
        type="number"
        step="0.01"
        placeholder="0.0"
        :disabled="disabled"
        :id="name"
        :name="name"
        v-model.number="v.fields.float.value"
        @input="v.fields.float.handleInput"  />
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
                v.fields.float.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.fields.float.resetField({
                    value: v.fields.float.value,
                });
            };

            // DATA
            const {
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`float_${name.value}`, yup.number(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    float: {
                        value: fieldValue,
                        handleInput,
                        meta,
                        resetField,
                    },
                },
            });

            watch(v.fields.float.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.float.meta.dirty,
                    valid: v.fields.float.meta.valid,
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
