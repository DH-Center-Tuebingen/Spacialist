<template>
    <simple-search
        :endpoint="searchEntity"
        :key-text="'name'"
        :default-value="v.fieldValue"
        @selected="e => entitySelected(e)" />
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

    import {
        searchEntity,
    } from '../../api.js';

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
                type: Object,
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
            const entitySelected = e => {
                const {
                    added,
                    removed,
                    ...entity
                } = e;
                let data;
                if(removed) {
                    data = null;
                } else if(added) {
                    data = entity;
                }
                v.handleChange(data);
            };
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.fieldValue,
                });
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`entity_${name.value}`, yup.mixed(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fieldValue,
                handleChange,
                meta,
                resetField,
                value: computed(_ => v.fieldValue ? v.fieldValue.id : null),
            });

            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                });
            });

            // RETURN
            return {
                // HELPERS
                searchEntity,
                // LOCAL
                entitySelected,
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
