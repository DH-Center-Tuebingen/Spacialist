 
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
        v-model="v.fields.date.value"
        @input="v.fields.date.handleInput">
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
                v.fields.date.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.fields.date.resetField({
                    value: v.fields.date.value,
                });
            };

            // DATA
            const {
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`date_${name.value}`, yup.date(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    date: {
                        value: fieldValue,
                        handleInput,
                        meta,
                        resetField,
                    },
                },
            });

            watch(v.fields.date.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.date.meta.dirty,
                    valid: v.fields.date.meta.valid,
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
