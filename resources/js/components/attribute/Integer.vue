<template>
    <input
        class="form-control"
        type="number"
        step="1"
        placeholder="0"
        :disabled="disabled"
        :id="name"
        :name="name"
        v-model.number="v.fields.int.value"
        @input="v.fields.int.handleInput" />
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

            // DATA
            const {
                handleInput,
                value: fieldValue,
                meta,
            } = useField(`int_${name.value}`, yup.number().integer(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    int: {
                        value: fieldValue,
                        handleInput,
                        meta,
                    },
                },
            });

            watch(v.fields.int.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.int.meta.dirty,
                    valid: v.fields.int.meta.valid,
                });
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
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
