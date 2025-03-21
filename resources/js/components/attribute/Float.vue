<template>
    <input
        :id="name"
        v-model.number="v.value"
        class="form-control"
        type="number"
        step="0.01"
        placeholder="0.0"
        :disabled="disabled"
        :name="name"
        @keydown="onKeydown"
    >
    <InputError
        :v="v"
        :error="errorMessage"
    />
</template>

<script>
    import {
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';
    import InputError from '@/components/forms/InputError.vue';
    
    import {useNumberInputHotkeys} from '@/composables/number-input-hotkeys.js';

    export default {
        components: {
            InputError,
        },
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
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: props.value
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
                errorMessage,
            } = useField(`float_${props.name}`, yup.number(), {
                initialValue: props.value,
            });
            
            const v = reactive({
                value: fieldValue,
                meta,
                resetField,
            });

            const { onKeydown } = useNumberInputHotkeys(v, true);

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
                v,
                errorMessage,
                onKeydown,
            };
        },
    };
</script>
