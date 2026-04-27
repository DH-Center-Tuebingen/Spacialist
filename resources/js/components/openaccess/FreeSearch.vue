<template>
    <div class="row h-100">
        <div class="col-8 h-100 overflow-hidden d-flex flex-column">
            <h3>
                Results
            </h3>
            <LoadingSpinner
                v-if="state.loading"
                size="2x"
                class="d-flex flex-row align-items-center justify-content-center h-100 w-100 bg-secondary bg-opacity-50 text-body fs-1 position-absolute start-50 top-50 translate-middle"
            />
            <div class="">
                <a
                    v-for="entityType in state.selectedEntityTypes"
                    :key="entityType.id"
                    href="#"
                    class="badge text-bg-secondary text-decoration-none me-1"
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
            <hr>
            <Pagination
                class="pb-2"
                :data="state.pages.pagination"
                :hide-navigation="true"
            />
            <div class="overflow-y-auto">
                <Card
                    v-for="entity in state.pages.results"
                    :key="entity.id"
                    class="bg-primary text-dark bg-opacity-25"
                    :entity="entity"
                />
            </div>
            <Pagination
                class="mt-2"
                :data="state.pages.pagination"
                :hide-metadata="true"
                size="sm"
                @goto="gotoPage"
            />
        </div>
        <div class="col-4 h-100 overflow-hidden">
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
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        computed,
        watch,
    } from 'vue';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        fetchAttributes,
        getFilterResults,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';
    
    import Card from '@/components/openaccess/Card.vue';
    import { LoadingSpinner } from 'dhc-components';

    export default {
        components: {
            Card,
            LoadingSpinner,
        },
        setup(props) {
            const { t } = useI18n();
            const entityStore = useEntityStore();

            // DATA
            const state = reactive({
                pages: {},
                loading: false,
                selectedEntityTypes: [],
                selectedAttributes: [],
                availableEntityTypes: entityStore.entityTypes,
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
            const fetchData = async () => {
                state.loading = true;
                const attributes = await fetchAttributes();
                state.availableAttributes = attributes.filter(a => a.attribute.datatype != 'system-separator');
                state.loading = false;
            };

            const wrapFilter = async (entityTypes, attributes) => {
                state.loading = true;
                const result = await getFilterResults(entityTypes, attributes);
                state.loading = false;
                return result;
            };

            const setResult = resData => {
                const {
                    data,
                    ...pagination
                } = resData;
                state.pages.pagination = {
                    ...pagination,
                    cleanLinks: pagination.links.slice(1, -1),
                };
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

                wrapFilter(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id), page).then(data => setResult(data));
            };

            // FETCH
            fetchData();

            // WATCHER
            watch(_ => state.selectedEntityTypes.length, (newValue, oldValue) => {
                wrapFilter(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id)).then(data => setResult(data));
            });
            watch(_ => state.selectedAttributes.length, (newValue, oldValue) => {
                wrapFilter(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id)).then(data => setResult(data));
            });

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
