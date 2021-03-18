<template>
    <div class="d-flex">
        <input
            class="form-range"
            type="range"
            step="1"
            min="0"
            max="100"
            :disabled="disabled"
            :id="name"
            :name="name"
            v-model="v.fields.perc.value"
            @input="v.fields.perc.handleInput" />
        <span class="ms-3">
            {{ v.fields.perc.value }}%
        </span>
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

            // DATA
            const {
                handleInput,
                value: fieldValue,
                meta,
            } = useField(`perc_${name.value}`, yup.number(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    perc: {
                        value: fieldValue,
                        handleInput,
                        meta,
                    },
                },
            });

            watch(v.fields.perc.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.perc.meta.dirty,
                    valid: v.fields.perc.meta.valid,
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
