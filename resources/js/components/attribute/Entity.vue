<template>
    <simple-search
        :endpoint="searchWrapper"
        :key-text="'name'"
        :chain="'ancestors'"
        :mode="state.mode"
        :default-value="v.fieldValue"
        @selected="e => entitySelected(e)"
        @entry-click="e => entryClicked(e)"
    />
    <router-link
        v-if="!hideLink && !multiple && v.value"
        :to="{name: 'entitydetail', params: {id: v.fieldValue.id}, query: state.query}" 
        class="btn btn-outline-secondary btn-sm mt-2"
    >
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
        searchEntityInTypes,
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
            searchIn: {
                type: Array,
                required: false,
                default: _ => [],
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
                searchIn,
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
                        data = {};
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
                    value: value.value || (multiple.value ? [] : {})
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.fieldValue,
                });
            };
            const searchWrapper = query => searchEntityInTypes(query, searchIn.value || []);

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`entity_${name.value}`, yup.mixed(), {
                initialValue: value.value || (multiple.value ? [] : {}),
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


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => [v.meta.dirty, v.meta.valid], ([newDirty, newValid], [oldDirty, oldValid]) => {
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
                t,
                // HELPERS
                // LOCAL
                entitySelected,
                entryClicked,
                resetFieldState,
                undirtyField,
                searchWrapper,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    };
</script>
