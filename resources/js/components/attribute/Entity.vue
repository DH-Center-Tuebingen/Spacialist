<template>
    <simple-search
        :endpoint="searchEntity"
        :key-text="'name'"
        :default-value="v.fieldValue"
        @selected="e => entitySelected(e)" />
    <router-link :to="{name: 'entitydetail', params: {id: v.fieldValue.id}, query: state.query}" v-if="v.value" class="btn btn-outline-secondary btn-sm mt-2">
        {{ t('main.entity.attributes.entity.go_to', {name: v.fieldValue.name}) }}
    </router-link>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        useRoute,
    } from 'vue-router';

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
            const { t } = useI18n();
            const route = useRoute();
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
                query: computed(_ => route.query),
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
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
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
