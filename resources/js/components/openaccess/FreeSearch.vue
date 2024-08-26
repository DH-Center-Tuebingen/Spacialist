<template>
    <div class="row h-100">
        <div class="col-8 h-100 overflow-hidden d-flex flex-column">
            <h3>
                Results
            </h3>
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
            <p v-if="state.pages.pagination">
                Displaying results <span class="fw-bold">{{ state.pages.pagination.from }} - {{ state.pages.pagination.to }}</span>
                of <span class="fw-bold">{{ state.pages.pagination.total }}</span> in total.
            </p>
            <div class="overflow-y-auto">
                <result-card
                    v-for="entity in state.pages.results"
                    :key="entity.id"
                    class="bg-primary text-dark bg-opacity-25"
                    :entity="entity"
                />
            </div>
            <nav
                v-if="state.pages.pagination"
                class="mt-2"
                aria-label="Search result pagination"
            >
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li
                        class="page-item"
                        :class="pageClass('first')"
                    >
                        <a
                            class="page-link"
                            href="#"
                            aria-label="First"
                            @click.prevent="gotoPage(1)"
                        >
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-angle-double-left" />
                            </span>
                        </a>
                    </li>
                    <li
                        class="page-item"
                        :class="pageClass('previous')"
                    >
                        <a
                            class="page-link"
                            href="#"
                            aria-label="Previous"
                            @click.prevent="gotoPage(state.pages.pagination.current_page - 1)"
                        >
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-chevron-left" />
                            </span>
                        </a>
                    </li>
                    <li
                        v-for="page in state.pages.pagination.cleanLinks"
                        :key="`page-${page.label}`"
                        class="page-item"
                        :class="pageClass(page.label)"
                    >
                        <a
                            class="page-link"
                            href="#"
                            @click.prevent="gotoPage(page.label)"
                        >
                            {{ page.label }}
                        </a>
                    </li>
                    <li
                        class="page-item"
                        :class="pageClass('next')"
                    >
                        <a
                            class="page-link"
                            href="#"
                            aria-label="Next"
                            @click.prevent="gotoPage(state.pages.pagination.current_page + 1)"
                        >
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-chevron-right" />
                            </span>
                        </a>
                    </li>
                    <li
                        class="page-item"
                        :class="pageClass('last')"
                    >
                        <a
                            class="page-link"
                            href="#"
                            aria-label="Last"
                            @click.prevent="gotoPage(state.pages.pagination.last_page)"
                        >
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-angle-double-right" />
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>
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

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        fetchEntityTypes,
        fetchAttributes,
        getFilterResults,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH
            fetchEntityTypes().then(data => {
                state.availableEntityTypes = data;
            });
            fetchAttributes().then(data => {
                state.availableAttributes = data.filter(a => a.attribute.datatype != 'system-separator');
            });

            // DATA
            const state = reactive({
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

                getFilterResults(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id), page).then(data => setResult(data));
            };
            const pageClass = label => {
                const list = [];
                switch(label) {
                    case 'first':
                        if(state.pages.pagination.current_page == 1) {
                            list.push('disabled');
                        }
                        break;
                    case 'previous':
                        if(!state.pages.pagination.prev_page_url) {
                            list.push('disabled');
                        }
                        break;
                    case 'last':
                        if(state.pages.pagination.current_page == state.pages.pagination.last_page) {
                            list.push('disabled');
                        }
                        break;
                    case 'next':
                        if(!state.pages.pagination.next_page_url) {
                            list.push('disabled');
                        }
                        break;
                    case '...':
                        list.push('disabled');
                        break;
                    default:
                        if(state.pages.pagination.current_page == label) {
                            list.push('active');
                        }
                        break;
                }
                return list;
            };

            // WATCHER
            watch(_ => state.selectedEntityTypes.length, (newValue, oldValue) => {
                getFilterResults(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id)).then(data => setResult(data));
            });
            watch(_ => state.selectedAttributes.length, (newValue, oldValue) => {
                getFilterResults(state.selectedEntityTypes.map(et => et.id), state.selectedAttributes.map(attr => attr.id)).then(data => setResult(data));
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
                pageClass,
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
