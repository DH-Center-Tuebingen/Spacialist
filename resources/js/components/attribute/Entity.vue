<template>
    <simple-search
        :value="v.value"
        :endpoint="searchWrapper"
        :key-fn="handleDisplayResult"
        :chain="'ancestors'"
        :mode="state.mode"
        :disabled="disabled"
        :infinite="true"
        :limit="10"
        :can-fetch-more="state.hasNextPage"
        :append-to-body="true"
        @change="changed"
        @entry-click="entity => entryClicked(entity)"
    />
    <router-link
        v-if="canShowLink"
        :to="{ name: 'entitydetail', params: { id: v.fieldValue?.id }, query: state.query }"
        class="btn btn-outline-secondary btn-sm mt-2"
    >
        {{ t('main.entity.attributes.entity.go_to', { name: v.fieldValue.name }) }}
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

    import router from '%router';

    import {
        only,
    } from '@/helpers/helpers.js';

    import {
        hasNextPage,
    } from '@/helpers/pagination.js';

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
                value,
                searchIn,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS

            const entryClicked = entity => {
                if(props.hideLink) return;

                router.push({
                    name: 'entitydetail',
                    params: {
                        id: entity.id,
                    },
                    query: route.query
                });
            };
            const handleDisplayResult = entity => {
                if(searchEntityInTypes.name == 'error.deleted_entity') {
                    return t('main.entity.attributes.tablsearchEntityInTypes.error.deleted_entity');
                }
                return entity?.name;
            };
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value || (props.multiple ? [] : {})
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.fieldValue || (props.multiple ? [] : {}),
                });
            };
            const searchWrapper = async query => {
                if(!query) {
                    state.page = 0;
                    return Promise.resolve([]);
                }
                if(state.lastQueryString.toLowerCase() != query.toLowerCase()) {
                    state.page = 0;
                }
                state.lastQueryString = query;
                state.page++;
                const pagination = await searchEntityInTypes(query, props.searchIn || [], state.page);

                state.hasNextPage = hasNextPage(pagination);
                return pagination.data;
            };

            const canShowLink = computed(_ => {
                if(props.hideLink || !v.fieldValue?.name) return false;
                return !props.multiple && v.fieldValue.name != 'error.deleted_entity';
            });

            const valueHasChanged = (v1, v2) => {
                if(!v1 && !v2) return false;
                if(!v1 || !v2) return true;
                if(v1.length != v2.length) return true;
                if(props.multiple) {
                    const l2 = v2.map(itm => itm.id);
                    for(let i=0; i<v1.length; i++) {
                        const itm1 = v1[i];
                        if(!l2.includes(itm1)) return true;
                    }
                    return false;
                } else {
                    return v1.id != v2.id;
                }
            };

            const changed = value => {
                v.handleChange(value);
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`entity_${props.name}`, yup.mixed().nullable(), {
                initialValue: value.value || (props.multiple ? [] : {}),
            });

            const state = reactive({
                query: computed(_ => route.query),
                mode: computed(_ => props.multiple ? 'tags' : 'single'),
                lastQueryString: '',
                page: 0,
                hasNextPage: false,
            });
            const v = reactive({
                fieldValue,
                handleChange,
                meta,
                resetField,
                value: computed(_ => {
                    if(!v.fieldValue || (!props.multiple && Object.keys(v.fieldValue).length == 0)) return (props.multiple ? [] : {});
                    let value = null;
                    if(v.fieldValue) {
                        if(props.multiple) {
                            value = v.fieldValue.map(fv => only(fv, ['id', 'name']));
                        } else {
                            value = only(v.fieldValue, ['id', 'name']);
                        }
                    }
                    return value;
                }),
            });

            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                if(!valueHasChanged(oldValue, newValue)) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // LOCAL
                canShowLink,
                entryClicked,
                handleDisplayResult,
                resetFieldState,
                undirtyField,
                searchWrapper,
                changed,
                // STATE
                state,
                v,
            };
        },
    };
</script>
