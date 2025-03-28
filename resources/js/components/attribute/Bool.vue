<template>
    <div class="form-check form-switch h-100 d-flex align-items-center">
        <input
            :id="name"
            v-model="v.value"
            class="form-check-input"
            type="checkbox"
            :disabled="disabled"
            :name="name"
        >
    </div>
</template>

<script>
    import {
        computed,
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
            const initValue = computed(_ => !!props.value);

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: initValue.value,
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            // DATA
            const {
                value: fieldValue,
                meta,
                resetField,
            } = useField(`perc_${props.name}`, yup.boolean(), {
                type: 'checkbox',
                valueProp: initValue.value,
                initialValue: initValue.value,
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                meta,
                resetField,
            });


            watch(_ => props.value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
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
            };
        },
    };
</script>
