<template>
    <input
        :id="name"
        v-model.number="v.value"
        class="form-control"
        type="number"
        step="1"
        placeholder="0"
        :disabled="disabled"
        :name="name"
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
            const validateValidation = value => {
                return Number.isInteger(value);
            };

            // DATA
            const {
                value: fieldValue,
                meta,
                resetField,
                validate,
            } = useField(`int_${name.value}`, yup.number().integer(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                meta,
                resetField,
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                // Apparently, invalid values are returned as empty value in HTML number inputs
                // Because it is not a required field, it returns true, because an empty value is a valid int
                // see https://github.com/logaretm/vee-validate/issues/1667#issuecomment-431992508
                if(!validateValidation(v.value)) {
                    context.emit('change', {
                        dirty: v.meta.dirty,
                        valid: false,
                        value: v.value,
                    });
                    return;
                }
                // also call validate() to make sure all values are up to date
                // without this, meta/errors still contain the value from last validation
                validate().then(validation => {
                    context.emit('change', {
                        dirty: v.meta.dirty,
                        valid: v.meta.valid,
                        value: v.value,
                    });
                })
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
            };
        },
    };
</script>
