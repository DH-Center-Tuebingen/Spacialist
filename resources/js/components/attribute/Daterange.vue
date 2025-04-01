<template>
    <DatePicker
        :id="name"
        :uid="name"
        v-model="v.value"
        class="w-100"
        input-class="form-control"
        value-type="date"
        :auto-apply="true"
        :clearable="true"
        :range="true"
        :multi-calendars="true"
        :name="name"
        :disabled="disabled"
        :enable-time-picker="false"
        :format="'dd.MM.yyyy'"
        :text-input="true"
        :utc="'preserve'"
        :week-numbers="{'type': 'iso'}"
        :week-num-name="''"
        @update:model-value="handleInput"
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
                type: Array,
                validator: arr => {
                    return !arr || arr.length === 2;
                },
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
            const resetFieldState = _ => {
                v.resetField({
                    value: fixValue(),
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };
            const handleInput = value => {
                v.handleChange(value);
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`daterange_${name.value}`, yup.array(), {
                initialValue: value.value || [],
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                handleChange,
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
                handleInput,
                // STATE
                state,
                v,
            };
        },
    };
</script>
