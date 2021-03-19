<template>
    <div class="form-check form-switch h-100 d-flex align-items-center">
        <input
            class="form-check-input"
            type="checkbox"
            :disabled="disabled"
            :id="name"
            :name="name"
            v-model="v.fields.bool.value"
            @input="v.fields.bool.handleInput" />
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
                v.fields.bool.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.fields.bool.resetField({
                    value: v.fields.bool.value,
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
                fields: {
                    bool: {
                        value: fieldValue,
                        handleInput,
                        meta,
                        resetField,
                    },
                },
            });

            watch(v.fields.bool.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.bool.meta.dirty,
                    valid: v.fields.bool.meta.valid,
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
