<template>
    <input
        class="form-control"
        type="text"
        :disabled="disabled"
        :id="name"
        :name="name"
        v-model="v.fields.str.value"
        @input="v.fields.str.handleInput" />
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
            } = useField(`str_${name.value}`, yup.string(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    str: {
                        value: fieldValue,
                        handleInput,
                        meta,
                    },
                },
            });

            watch(v.fields.str.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.str.meta.dirty,
                    valid: v.fields.str.meta.valid,
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
