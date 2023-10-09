<template>
    <div class="row">
        <h3>
            <a href="#" class="text-decoration-none text-muted" @click.prevent="unsetEntityType()" v-show="state.selectedEntityType">
                <i class="fa fa-fw fa-arrow-turn-up fa-flip-horizontal fa-xs"></i>
            </a>
            Single Search
            <small v-if="state.selectedEntityType">
                -
                {{ translateConcept(state.selectedEntityType.thesaurus_url) }}
            </small>
        </h3>
    </div>
    <div class="row flex-grow-1 overflow-hidden">
        <div class="col-12 h-100 overflow-hidden" v-if="!state.selectedEntityType">
            <p class="lead">
                Please select an entity type first to search through the database
            </p>
            <ul class="list-group">
                <li class="list-group-item" v-for="entityType in state.availableEntityTypes" :key="entityType.id">
                    <a href="#" class="text-decoration-none" @click.prevent="selectEntityType(entityType)">
                        {{ translateConcept(entityType.thesaurus_url) }}
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-8 h-100 overflow-hidden d-flex flex-column" v-if="state.selectedEntityType">
            <p v-if="state.pages.pagination">
                Displaying results <span class="fw-bold">{{ state.pages.pagination.from }} - {{ state.pages.pagination.to }}</span> of <span class="fw-bold">{{ state.pages.pagination.total }}</span> in total.
            </p>
            <div class="scroll-y-auto">
                <result-card
                    class="bg-primary text-dark bg-opacity-25"
                    v-for="entry in state.pages.data" :key="entry.id"
                    :entity="entry"
                />
            </div>
            <nav class="mt-2" aria-label="Search result pagination" v-if="state.pages.pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item" :class="pageClass('first')">
                        <a class="page-link" href="#" aria-label="First" @click.prevent="gotoPage(1)">
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-angle-double-left"></i>
                            </span>
                        </a>
                    </li>
                    <li class="page-item" :class="pageClass('previous')">
                        <a class="page-link" href="#" aria-label="Previous" @click.prevent="gotoPage(state.pages.pagination.current_page - 1)">
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-chevron-left"></i>
                            </span>
                        </a>
                    </li>
                    <li class="page-item" :class="pageClass(page.label)" v-for="page in state.pages.pagination.cleanLinks" :key="`page-${page.label}`">
                        <a class="page-link" href="#" @click.prevent="gotoPage(page.label)">
                            {{ page.label }}
                        </a>
                    </li>
                    <li class="page-item" :class="pageClass('next')">
                        <a class="page-link" href="#" aria-label="Next" @click.prevent="gotoPage(state.pages.pagination.current_page + 1)">
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-chevron-right"></i>
                            </span>
                        </a>
                    </li>
                    <li class="page-item" :class="pageClass('last')">
                        <a class="page-link" href="#" aria-label="Last" @click.prevent="gotoPage(state.pages.pagination.last_page)">
                            <span aria-hidden="true">
                                <i class="fas fa-fw fa-angle-double-right"></i>
                            </span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="col-4 h-100 overflow-hidden d-flex flex-column" v-if="state.selectedEntityType">
            <h4>Filters</h4>
            <div class="d-flex flex-column scroll-y-auto flex-grow-1">
                <div v-for="attribute in state.filterableAttributes.attributes" :key="attribute.id" class="mb-3">
                    <h5>
                        {{ translateConcept(attribute.attribute.thesaurus_url) }}
                        <small>
                            <span class="badge bg-primary">
                                {{ state.filterableAttributes.data_count[attribute.attribute_id] }}
                            </span>
                        </small>
                    </h5>
                    <multiselect
                        v-model="state.attributeDataModels[attribute.attribute_id]"
                        :disabled="state.filterableAttributes.data_count[attribute.attribute_id] == 0"
                        :label="'key'"
                        :valueProp="'key'"
                        :track-by="'key'"
                        :object="true"
                        :mode="'tags'"
                        :hideSelected="false"
                        :closeOnSelect="false"
                        :options="state.filterableAttributes.data[attribute.attribute_id]"
                        @change="option => handleFilterChange(attribute.attribute_id, option)"
                    >
                        <template v-slot:beforelist>
                            <a href="#" class="py-2 text-reset text-decoration-none" style="padding-left: 0.75rem; padding-right: 0.75rem;" @click.prevent="resetFilter(attribute.attribute_id)">
                                All Values
                            </a>
                        </template>
                        <template v-slot:option="{option}">
                            <div class="d-flex flex-row w-100 justify-content-between">
                                <span v-if="attribute.attribute.datatype == 'string-sc'">
                                    {{ translateConcept(option.key) }}
                                </span>
                                <span v-else>
                                    {{ option.key }}
                                </span>
                                <span class="fw-bold">
                                    [{{ option.count }}]
                                </span>
                            </div>
                        </template>
                        <template v-slot:tag="{option, handleTagRemove, disabled}">
                            <div class="multiselect-tag">
                                <span v-if="attribute.attribute.datatype == 'string-sc'">
                                    {{ translateConcept(option.key) }}
                                </span>
                                <span v-else>
                                    {{ option.key }}
                                </span>
                                <span v-if="!disabled" class="multiselect-tag-remove" @click.prevent @mousedown.prevent.stop="handleTagRemove(option, $event)">
                                    <span class="multiselect-tag-remove-icon"></span>
                                </span>
                            </div>
                        </template>
                    </multiselect>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        computed,
        onMounted,
        watch,
    } from 'vue';

    import {
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        fetchEntityTypes,
        fetchAttributes,
        getFilterResultsForType,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH
            fetchEntityTypes().then(data => {
                state.availableEntityTypes = data;
            });

            // DATA
            const state = reactive({
                allAttributesData: {},
                pages: {},
                availableEntityTypes: [],
                availableAttributes: {},
                filterableAttributes: computed(_ => {
                    const data = {
                        attributes: [],
                        data: {},
                        data_count: {},
                    };

                    if(!state.availableAttributes.attributes) {
                        return data;
                    }

                    for(let i=0; i<state.availableAttributes.attributes.length; i++) {
                        const currAttr = state.availableAttributes.attributes[i];
                        const currData = state.availableAttributes.data[currAttr.attribute_id];

                        if(!state.attributeDataModels[currAttr.attribute_id]) {
                            state.attributeDataModels[currAttr.attribute_id] = [];
                        }

                        data.attributes.push(currAttr);
                        data.data[currAttr.attribute_id] = currData ? Object.keys(currData).map(k => {
                            return {
                                'key': k,
                                'count': currData[k],
                            };
                        }) : [];
                        data.data_count[currAttr.attribute_id] = currData ? Object.keys(currData).length : 0;
                    }

                    return data;
                }),
                selectedEntityType: computed(_ => {
                    if(state.selectedEntityTypeId) {
                        return state.availableEntityTypes.find(et => et.id == state.selectedEntityTypeId);
                    } else {
                        return null;
                    }
                }),
                selectedEntityTypeId: null,
                attributeDataModels: {},
                filters: {},
            });

            // FUNCTIONS
            const selectEntityType = entityType => {
                state.selectedEntityTypeId = entityType.id;
            };
            const unsetEntityType = _ => {
                state.selectedEntityTypeId = null;
                state.pages = {};
            };
            const setResultData = (pagData, initial = false) => {
                const {
                    data,
                    ...pagination
                } = pagData.entities;
                if(initial) {
                    state.availableAttributes.data = pagData.data;
                    state.allAttributesData = _cloneDeep(pagData.data);
                } else {
                    // TODO
                    for(let k in state.allAttributesData) {
                        if(state.filters[k]) {
                            state.availableAttributes.data[k] = state.allAttributesData[k];
                        } else {
                            state.availableAttributes.data[k] = pagData.data[k] || [];
                        }
                    }
                }

                state.pages.data = data;
                state.pages.pagination = {
                    ...pagination,
                    cleanLinks: pagination.links.slice(1, -1),
                };
            };
            const resetFilter = attributeId => {
                delete state.filters[attributeId];
                state.attributeDataModels[attributeId] = [];
            };
            const handleFilterChange = (attributeId, options) => {
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
            watch(_ => state.selectedEntityTypeId, (newValue, oldValue) => {
                if(newValue) {
                    fetchAttributes(state.selectedEntityTypeId, true).then(data => {
                        for(let i=0; i<data.attributes.length; i++) {
                            const curr = data.attributes[i];
                            state.attributeDataModels[curr.attribute_id] = [];
                        }
                        state.availableAttributes = data;
                    });
                    getFilterResultsForType(state.selectedEntityTypeId).then(data => {
                        setResultData(data, true);
                    });
                }
            });
            watch(state.filters, (newValue, oldValue) => {
                // TODO update date from "getAttributes" for updated entity list
                getFilterResultsForType(state.selectedEntityTypeId, state.filters).then(data => {
                    setResultData(data);
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                selectEntityType,
                unsetEntityType,
                resetFilter,
                handleFilterChange,
                gotoPage,
                pageClass,
                // PROPS
                // STATE
                state,
            };
        }
    }
</script>
