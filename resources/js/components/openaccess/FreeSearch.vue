<template>
    <SearchView
        title="Free Search"
        :loading="state.loading"
        :pagination="state.pages.pagination"
        @page-selected="gotoPage"
    >
        <template #test>
            <div>
                <div class="d-flex gap-2">
                    <a
                        v-for="entityType in state.selectedEntityTypes"
                        :key="entityType.id"
                        href="#"
                        class="badge text-bg-secondary text-decoration-none"
                        @click.prevent="removeEntityTypeFilter(entityType.id)"
                    >
                        <i class="fas fa-fw fa-monument" />
                        {{ translateConcept(entityType.thesaurus_url) }}
                        <i class="fas fa-fw fa-times" />
                    </a>
                    <a
                        v-for="attribute in state.selectedAttributes"
                        :key="attribute.id"
                        href="#"
                        class="badge text-bg-secondary text-decoration-none me-1"
                        @click.prevent="removeAttributeFilter(attribute.id)"
                    >
                        <i class="fas fa-fw fa-sitemap" />
                        {{ translateConcept(attribute.attribute.thesaurus_url) }}
                        <i class="fas fa-fw fa-times" />
                    </a>
                </div>
            </div>
        </template>
        <template #results>
            <result-card
                v-for="entity in state.pages.results"
                :key="entity.id"
                class="bg-primary text-dark bg-opacity-25"
                :entity="entity"
            />
        </template>
        <template #filters>
            <h4>Filter</h4>
            <div class="mb-2">
                <h5 class="mb-0">
                    Entitätstypen
                </h5>
                <div>
                    <a
                        v-for="entityType in state.selectableEntityTypes"
                        :key="entityType.id"
                        href="#"
                        class="badge text-bg-primary text-decoration-none me-1"
                        @click.prevent="addEntityTypeFilter(entityType.id)"
                    >
                        {{ translateConcept(entityType.thesaurus_url) }}
                        <i class="fas fa-fw fa-plus" />
                    </a>
                </div>
            </div>
            <div class="mb-2">
                <h5 class="mb-0">
                    Eigenschaften
                </h5>
                <div>
                    <a
                        v-for="attribute in state.selectableAttributes"
                        :key="attribute.id"
                        href="#"
                        class="badge text-bg-primary text-decoration-none me-1"
                        @click.prevent="addAttributeFilter(attribute.id)"
                    >
                        {{ translateConcept(attribute.attribute.thesaurus_url) }}
                        <i class="fas fa-fw fa-plus" />
                    </a>
                </div>
            </div>
        </template>
    </SearchView>
</template>

<script>
    import {
        reactive,
        computed,
        watch,
    } from 'vue';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        fetchEntityTypes,
        fetchAttributes,
        getFilterResults,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';
    import SearchView from '../view/open/SearchView.vue';

    export default {
        components: {
            SearchView,
        },
        setup(props) {
            const { t } = useI18n();

            const load = async _ => {
                const entityTypes = await fetchEntityTypes();
                const attributes = await fetchAttributes();
                state.availableEntityTypes = entityTypes;
                state.availableAttributes = attributes.filter(a => a.attribute.datatype != 'system-separator');
                state.loading = false;
                updateResults();
            };

            load();

            // DATA
            const state = reactive({
                loading: true,
                pages: {},
                selectedEntityTypes: [],
                selectedAttributes: [],
                availableEntityTypes: [],
                availableAttributes: [],
                selectableEntityTypes: computed(_ => {
                    let list = state.availableEntityTypes.filter(et => {
                        return !state.selectedEntityTypeIds.includes(et.id);
                    });
                    // TODO 
                    // if(state.selectedAttributes.length > 0) {
                    //     list = list.filter(et => {
                    //         return state.selectedEntityTypeAttributeIds.includes(et.id);
                    //     });
                    // }
                    return list;
                }),
                selectableAttributes: computed(_ => {
                    let list = state.availableAttributes.filter(attr => {
                        return !state.selectedAttributeIds.includes(attr.id);
                    });
                    // TODO 
                    // if(state.selectedEntityTypes.length > 0) {
                    //     list = list.filter(attr => {
                    //         return state.selectedEntityTypeIds.includes(attr.entity_type_id);
                    //     });
                    // }
                    return list;
                }),
                selectedEntityTypeIds: computed(_ => state.selectedEntityTypes.map(et => et.id)),
                selectedEntityTypeAttributeIds: computed(_ => state.selectedAttributes.map(attr => attr.entity_type_id)),
                selectedAttributeIds: computed(_ => state.selectedAttributes.map(attr => attr.id)),
            });

            // FUNCTIONS
            const setResult = resData => {
                const {
                    data,
                    ...pagination
                } = resData;
                state.pages.pagination = pagination;
                state.pages.results = data;
            };
            const addEntityTypeFilter = id => {
                const idx = state.availableEntityTypes.findIndex(et => et.id == id);
                if(idx > -1) {
                    state.selectedEntityTypes.push(state.availableEntityTypes[idx]);
                }
            };
            const removeEntityTypeFilter = id => {
                const idx = state.selectedEntityTypes.findIndex(et => et.id == id);
                if(idx > -1) {
                    state.selectedEntityTypes.splice(idx, 1);
                }
            };
            const addAttributeFilter = id => {
                const idx = state.availableAttributes.findIndex(attr => attr.id == id);
                if(idx > -1) {
                    state.selectedAttributes.push(state.availableAttributes[idx]);
                }
            };
            const removeAttributeFilter = id => {
                const idx = state.selectedAttributes.findIndex(attr => attr.id == id);
                if(idx > -1) {
                    state.selectedAttributes.splice(idx, 1);
                }
            };
            const gotoPage = page => {
                if(page == '...' || state.pages.pagination.current_page == page) {
                    return;
                }

                getFilterResults(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id), page).then(data => setResult(data));
            };


            function updateResults () {
                getFilterResults(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id)).then(data => setResult(data));
            }

            // WATCHER
            watch(_ => state.selectedEntityTypes.length, updateResults);
            watch(_ => state.selectedAttributes.length, updateResults);

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                addEntityTypeFilter,
                removeEntityTypeFilter,
                addAttributeFilter,
                removeAttributeFilter,
                gotoPage,
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
