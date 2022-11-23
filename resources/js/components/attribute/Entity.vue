<template>
    <simple-search
        :endpoint="searchEntity"
        :key-text="'name'"
        :chain="'ancestors'"
        :mode="state.mode"
        :default-value="v.fieldValue"
        @selected="e => entitySelected(e)"
        @entry-click="e => entryClicked(e)" />
    <router-link v-if="!hideLink && !multiple && v.value" :to="{name: 'entitydetail', params: {id: v.fieldValue.id}, query: state.query}" class="btn btn-outline-secondary btn-sm mt-2">
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

    import router from '@/bootstrap/router.js';

    import {
        searchEntity,
    } from '@/api.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            multiple: {
                type: Boolean,
                required: false,
                default: false,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            hideLink: {
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
                multiple,
                disabled,
                hideLink,
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
                    if(multiple.value) {
                        data = entity.values;
                    } else {
                        data = null;
                    }
                } else if(added) {
                    if(multiple.value) {
                        data = entity.values;
                    } else {
                        data = entity;
                    }
                }
                v.handleChange(data);
            };
            const entryClicked = e => {
                if(hideLink.value) return;

                router.push({
                    name: 'entitydetail',
                    params: {
                        id: e.id,
                    },
                    query: route.query
                });
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
                mode: computed(_ => multiple.value ? 'tags' : 'single'),
            });
            const v = reactive({
                fieldValue,
                handleChange,
                meta,
                resetField,
                value: computed(_ => {
                    let value = null;
                    if(v.fieldValue) {
                        if(multiple.value) {
                            value = v.fieldValue.map(fv => fv.id);
                        } else {
                            value = v.fieldValue.id;
                        }
                    }
                    return value;
                }),
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
                entryClicked,
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                multiple,
                disabled,
                hideLink,
                value,
                // STATE
                state,
                v,
            }
        },
    }
</script>
