<template>
    <date-picker
        :id="name"
        v-model:value="v.value"
        class="w-100"
        input-class="form-control"
        value-type="date"
        :name="name"
        :disabled="disabled"
        :show-week-number="true"
        @change="handleInput"
    >
        <template #icon-calendar>
            <i class="fas fa-fw fa-calendar-alt" />
        </template>
        <template #icon-clear>
            <i class="fas fa-fw fa-times" />
        </template>
    </date-picker>
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
                type: String,
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
                    value: value.value ? new Date(value.value) : null,
                });
            };
            const undirtyField = _ => {
                // v.value is already a date or null
                v.resetField({
                    value: v.value,
                });
            };
            const handleInput = value => {
                if(!value) {
                    v.handleChange(value);
                } else {
                    // add timezone offset before handle change
                    const correctValue = new Date(value.getTime() - (value.getTimezoneOffset()*60*1000));
                    v.handleChange(correctValue);
                }
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`date_${name.value}`, yup.date().nullable(), {
                initialValue: value.value ? new Date(value.value) : null,
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
