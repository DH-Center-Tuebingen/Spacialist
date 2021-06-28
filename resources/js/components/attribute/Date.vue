 
<template>
    <date-picker
        class="w-100"
        input-class="form-control"
        value-type="date"
        :id="name"
        :name="name"
        :disabled="disabled"
        :disabled-date="(date) => date > new Date()"
        :max-date="new Date()"
        :show-week-number="true"
        v-model="v.value"
        @input="handleInput">
        <template v-slot:icon-calendar>
            <i class="fas fa-fw fa-calendar-alt"></i>
        </template>
        <template v-slot:icon-clear>
            <i class="fas fa-fw fa-times"></i>
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
                    value: new Date(value.value)
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: new Date(v.value),
                });
            };
            const handleInput = value => {
                // add timezone offset before handle change
                const correctValue = new Date(value.getTime() - (value.getTimezoneOffset()*60*1000));
                v.handleChange(correctValue);
            }

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`date_${name.value}`, yup.date(), {
                initialValue: new Date(value.value),
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });

            watch(v.meta, (newValue, oldValue) => {
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
