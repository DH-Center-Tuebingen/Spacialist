<template>
    <div class="row">
        <h3>
            <a
                href="#"
                class="text-decoration-none text-muted"
                @click.prevent="goBack"
            >
                <i class="fa fa-fw fa-arrow-turn-up fa-flip-horizontal fa-xs" />
            </a>
            Single Search
            <small v-if="state.selectedEntityType">
                -
                {{ translateConcept(state.selectedEntityType.thesaurus_url) }}
            </small>
        </h3>
    </div>
    <div class="row flex-grow-1 overflow-hidden">
        <ResultsPanel
            :data="state.pages.data || []"
            :pagination="state.pages.pagination || {}"
            @goto="gotoPage"
        />
        <FilterPanel
            :filters="state.filterpanelFilters"
            :filterable-attributes="state.filterableAttributes"
            :attributes="state.availableAttributes"
            :attribute-values="state.attributeValues"
            @reset-filter="resetFilter"
            @change-filter="handleFilterChange"
        />
    </div>
</template>

<script>
    import {
        reactive,
        computed,
        watch,
    } from 'vue';

    import { useRoute, useRouter } from 'vue-router';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        fetchAttributes,
        fetchAttributeValuesForEntityType,
        getFilterResultsForType,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    import ResultsPanel from '@/components/openaccess/single-search/ResultsPanel.vue';
    import FilterPanel from '@/components/openaccess/single-search/FilterPanel.vue';

    export default {
        components: {
            ResultsPanel,
            FilterPanel,
        },
        setup() {
            const { t } = useI18n();
            const route = useRoute();
            const router = useRouter();
            const entityStore = useEntityStore();

            const getSelectedEntityTypeId = () => Number(route.params.entityTypeId || 0);

            const state = reactive({
                allAttributesData: {},
                pages: {},
                availableAttributes: {},
                filterpanelFilters: {},
                attributeValues: {},
                filters: {},
                filterpanelFilters: {},
                selectedEntityTypeId: computed(() => getSelectedEntityTypeId()),
                selectedEntityType: computed(() => {
                    if(!state.selectedEntityTypeId) {
                        return null;
                    }
                    return entityStore.entityTypes[state.selectedEntityTypeId] || null;
                }),
                //// [SO] I would argue for the prototype that is way to complicated
                //// this caused the error that only loaded entities were included in the filter options
                //// I get the idea that it would be nice to have only available options for the filters,
                //// but I see it as an advanced feature that can be added later.
                //
                // filterableAttributes: computed(() => {
                //     const data = {
                //         attributes: [],
                //         data: {},
                //         data_count: {},
                //     };

                //     if(!state.availableAttributes.attributes) {
                //         return data;
                //     }

                //     for(let i=0; i<state.availableAttributes.attributes.length; i++) {
                //         const currAttr = state.availableAttributes.attributes[i];

                //         if(currAttr.attribute.datatype == 'system-separator') continue;

                //         const currData = state.availableAttributes.data[currAttr.attribute_id];

                //         if(!state.filterpanelFilters[currAttr.attribute_id]) {
                //             state.filterpanelFilters[currAttr.attribute_id] = [];
                //         }

                //         data.attributes.push(currAttr);
                //         data.data[currAttr.attribute_id] = currData ? Object.keys(currData).map(k => {
                //             return {
                //                 key: k,
                //                 count: currData[k],
                //             };
                //         }) : [];
                //         data.data_count[currAttr.attribute_id] = currData ? Object.keys(currData).length : 0;
                //     }

                //     return data;
                // }),
            });

            const hydrateEntityTypeData = async entityTypeId => {
                if(!entityTypeId) {
                    return;
                }

                // fetchAttributes(entityTypeId, true).then(data => {
                //     state.availableAttributes = data;
                //     state.filterpanelFilters = {};
                //     for(let i=0; i<data.attributes.length; i++) {
                //         const curr = data.attributes[i];
                //         state.filterpanelFilters[curr.attribute_id] = [];
                //     }
                // });

                const attributes = await fetchAttributes(entityTypeId);
                state.availableAttributes = attributes.filter(a => a.attribute.datatype != 'system-separator');;

                state.attributeValues = await fetchAttributeValuesForEntityType(entityTypeId);

                const data = await getFilterResultsForType(entityTypeId)
                setResultData(data, true);
            };

            const setResultData = (pagData, initial = false) => {
                console.log(pagData);
                const {
                    data,
                    ...pagination
                } = pagData;

                state.pages.data = data;
                state.pages.pagination = pagination;
            };

            const resetFilter = attributeId => {
                delete state.filters[attributeId];
                state.filterpanelFilters[attributeId] = [];
            };

            const handleFilterChange = (attributeId, options) => {
                console.log(attributeId, options);
                state.filterpanelFilters[attributeId] = options;
                if(options.length > 0) {
                    state.filters[attributeId] = options.map(o => o.key);
                } else {
                    resetFilter(attributeId);
                }
            };

            const gotoPage = page => {
                if(page == '...' || state.pages.pagination.current_page == page) {
                    return;
                }

                getFilterResultsForType(state.selectedEntityTypeId, state.filters, page).then(data => {
                    setResultData(data);
                });
            };

            const goBack = () => {
                router.push({ name: 'singlesearch' });
            };

            watch(() => route.params.entityTypeId, _ => {
                state.pages = {};
                state.availableAttributes = {};
                state.allAttributesData = {};
                state.filters = {};
                hydrateEntityTypeData(state.selectedEntityTypeId);
            }, { immediate: true });

            watch(state.filters, () => {
                if(!state.selectedEntityTypeId) {
                    return;
                }
                getFilterResultsForType(state.selectedEntityTypeId, state.filters).then(data => {
                    setResultData(data);
                });
            });

            return {
                t,
                translateConcept,
                state,
                gotoPage,
                goBack,
                resetFilter,
                handleFilterChange,
            };
        },
    };
</script>
