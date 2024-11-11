<template>
    <div
        v-dcan="'entity_data_write|entity_data_read'"
        class="row d-flex flex-row overflow-hidden h-100"
    >
        <div class="col-md-2 py-2 h-100 d-flex flex-column bg-light-dark">
            <h4 class="d-flex flex-row gap-2 align-items-center">
                {{ t('main.datamodel.entity.title') }}
                <button
                    type="button"
                    class="btn btn-outline-success btn-sm"
                    @click="addEntityType()"
                >
                    <i class="fas fa-fw fa-plus" /> {{ t('main.datamodel.entity.add_button') }}
                </button>
            </h4>
            <EntityTypeList
                class="col px-0 h-100 d-flex flex-column"
                :data="state.entityTypes"
                :selected-id="state.selectedEntityType"
                @delete-element="requestDeleteEntityType"
                @duplicate-element="duplicateEntityType"
                @edit-element="editEntityType"
                @select-element="setEntityType"
            />
        </div>
        <div class="col-md-6 py-2 h-100 bg-light-dark rounded-end ">
            <router-view />
        </div>
        <div class="col-md-4 py-2 h-100 d-flex flex-column">
            <div class="d-flex flex-row justify-content-between">
                <h4 class="d-flex flex-row gap-2 align-items-center">
                    {{ t('main.datamodel.attribute.title') }}
                    <button
                        type="button"
                        class="btn btn-outline-success btn-sm"
                        @click="createAttribute()"
                    >
                        <i class="fas fa-fw fa-plus" /> {{ t('main.datamodel.attribute.add_button') }}
                    </button>
                </h4>
                <div class="dropdown">
                    <span
                        id="dme-attribute-list-options-dropdown"
                        class="clickable text-body align-middle"
                        data-bs-toggle="dropdown"
                        role="button"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="fas fa-fw fa-ellipsis-vertical" />
                    </span>
                    <div
                        class="dropdown-menu"
                        aria-labelledby="dme-attribute-list-options-dropdown"
                    >
                        <a
                            href="#"
                            class="dropdown-item"
                            @click.prevent="state.showHiddenAttributes = !state.showHiddenAttributes"
                        >
                            <span v-show="state.showHiddenAttributes">
                                <i class="fas fa-fw fa-eye-slash" />
                                {{ t('main.datamodel.attribute.hide_hidden') }}
                            </span>
                            <span v-show="!state.showHiddenAttributes">
                                <i class="fas fa-fw fa-eye" />
                                {{ t('main.datamodel.attribute.show_hidden') }}
                            </span>
                        </a>
                        <a
                            href="#"
                            class="dropdown-item"
                            @click.prevent="state.showAttributesInGroups = !state.showAttributesInGroups"
                        >
                            <span v-show="state.showAttributesInGroups">
                                <i class="fas fa-fw fa-list" />
                                {{ t('main.datamodel.attribute.separated') }}
                            </span>
                            <span v-show="!state.showAttributesInGroups">
                                <i class="fas fa-fw fa-table-list" />
                                {{ t('main.datamodel.attribute.in_groups') }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col overflow-hidden mt-2 d-flex flex-column">
                <attribute-list
                    :group="{name: 'attribute-selection', pull: true, put: false}"
                    :classes="'mx-2 px-2 py-3 rounded-3 bg-secondary bg-opacity-10'"
                    :attributes="state.systemAttributeList"
                    :values="[]"
                    :options="{'hide_labels': true}"
                    :selections="{}"
                    :is-source="true"
                />
                <hr>
                <div class="d-flex flex-row justify-content-between align-items-center mb-2">
                    <div class="input-group w-50">
                        <span
                            id="dme-attribute-search"
                            class="input-group-text"
                        >
                            <i class="fas fa-fw fa-search" />
                        </span>
                        <input
                            v-model="state.attributeQuery"
                            :placeholder="t('global.search')"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div
                        class="btn-group btn-group-sm"
                        role="group"
                    >
                        <button
                            v-show="state.showAttributesInGroups"
                            type="button"
                            class="btn btn-outline-primary"
                            :title="t('main.datamodel.expand_groups')"
                            @click="setAttributeGroupExpand(true)"
                        >
                            <i class="fas fa-fw fa-angles-down" />
                        </button>
                        <button
                            v-show="state.showAttributesInGroups"
                            type="button"
                            class="btn btn-outline-primary"
                            :title="t('main.datamodel.collapse_groups')"
                            @click="setAttributeGroupExpand(false)"
                        >
                            <i class="fas fa-fw fa-angles-up" />
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            :class="{'active': state.sortAttributes}"
                            @click="state.sortAttributes = !state.sortAttributes"
                        >
                            <span
                                v-show="state.sortAttributes"
                                :title="t('main.datamodel.sort_by_name')"
                            >
                                <i class="fas fa-fw fa-arrow-down-a-z" />
                            </span>
                            <span
                                v-show="!state.sortAttributes"
                                :title="t('main.datamodel.sort_by_creation_date')"
                            >
                                <i class="fas fa-fw fa-arrow-down-1-9" />
                            </span>
                        </button>
                    </div>
                </div>
                <div
                    v-if="state.showAttributesInGroups && state.attributeList.length > 0"
                    class="col overflow-hidden d-flex flex-column"
                >
                    <div
                        id="dme-attribute-list-accordion"
                        ref="accordionRef"
                        class="accordion accordion-flush flex-grow-1 overflow-y-auto overflow-x-hidden pe-2"
                    >
                        <template
                            v-for="([type, attrGrp], index) in state.sortedAttributeListGroups"
                            :key="`dme-attribute-list-${type}-grp`"
                        >
                            <div
                                v-show="attributeGroupHasItems(attrGrp)"
                                class="accordion-item"
                                :class="{
                                    'border-bottom': index == state.sortedAttributeListGroups.length - 1,
                                }"
                            >
                                <h2 class="accordion-header">
                                    <button
                                        class="accordion-button"
                                        :class="{'text-muted': attributeGroupItemCount(attrGrp) == 0}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        :data-bs-target="`#dme-attribute-list-${type}-grp-container`"
                                        aria-expanded="false"
                                        :aria-controls="`dme-attribute-list-${type}-grp-container`"
                                    >
                                        <span>
                                            {{ t(`global.attributes.${type}`) }}
                                        </span>
                                        <span
                                            class="badge bg-primary ms-2 d-flex flex-row"
                                            :class="{'bg-opacity-50': attributeGroupItemCount(attrGrp) == 0}"
                                        >
                                            <span>{{ attributeGroupItemCount(attrGrp) }}</span>
                                            <span>/</span>
                                            <span>{{ attrGrp.length }}</span>
                                        </span>
                                    </button>
                                </h2>
                                <div
                                    :id="`dme-attribute-list-${type}-grp-container`"
                                    class="accordion-collapse collapse show"
                                >
                                    <div class="accordion-body px-2 py-3">
                                        <attribute-list
                                            :group="{name: `attribute-selection-${type}`, pull: true, put: false}"
                                            :attributes="attrGrp"
                                            :hidden-attributes="state.selectedEntityTypeAttributeIds"
                                            :show-hidden="state.showHiddenAttributes"
                                            :values="state.attributeListValues"
                                            :selections="{}"
                                            :is-source="true"
                                            :show-info="true"
                                            @delete-element="onDeleteAttribute"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <attribute-list
                    v-else-if="!state.showAttributesInGroups"
                    :classes="'pe-2 col overflow-y-auto overflow-x-hidden'"
                    :group="{name: 'attribute-selection', pull: true, put: false}"
                    :attributes="state.attributeList"
                    :hidden-attributes="state.selectedEntityTypeAttributeIds"
                    :show-hidden="state.showHiddenAttributes"
                    :values="state.attributeListValues"
                    :selections="{}"
                    :is-source="true"
                    :show-info="true"
                    @delete-element="onDeleteAttribute"
                />
                <Alert
                    v-if="state.attributeList.length == 0"
                    class="mb-0"
                    :message="`${t('global.search_no_results_for')} ${state.attributeQuery}`"
                    :type="'info'"
                    :noicon="false"
                    :icontext="t('global.information')"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        useRoute,
    } from 'vue-router';

    import store from '@/bootstrap/store.js';
    import router from '%router';

    import {
        duplicateEntityType as duplicateEntityTypeApi,
        getEntityTypeOccurrenceCount,
        getAttributeOccurrenceCount,
    } from '@/api.js';

    import {
        getEntityTypeAttributes,
        getInitialAttributeValue,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        showAddEntityType,
        showDeleteEntityType,
        showAddAttribute,
        showDeleteAttribute,
        showEditEntityType,
    } from '@/helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();

            // FETCH

            // FUNCTIONS
            const setEntityType = event => {
                router.push({
                    name: 'dmdetail',
                    params: {
                        id: event.type.id
                    }
                });
            };
            const addEntityType = _ => {
                showAddEntityType();
            };
            const duplicateEntityType = event => {
                const attrs = getEntityTypeAttributes(event.id).slice();
                duplicateEntityTypeApi(event.id).then(data => {
                    data.attributes = attrs;
                    store.dispatch('addEntityType', data);
                });
            };
            const editEntityType = e => {
                showEditEntityType(e.type);
            };
            const requestDeleteEntityType = event => {
                getEntityTypeOccurrenceCount(event.type.id).then(data => {
                    const metadata = {
                        entityCount: data,
                    };
                    showDeleteEntityType(event.type, metadata, _ => {
                        if(currentRoute.name == 'dmdetail' && currentRoute.params.id == event.type.id) {
                            router.push({
                                name: 'dme',
                            });
                        }
                    });
                });
            };
            const createAttribute = _ => {
                showAddAttribute();
            };
            const onDeleteAttribute = e => {
                const attribute = e.element;
                getAttributeOccurrenceCount(attribute.id).then(data => {
                    const metadata = {
                        attributeCount: data,
                    };
                    showDeleteAttribute(attribute, metadata);
                });
            };
            const setAttributeGroupExpand = (expand = true) => {
                const parent = accordionRef.value;
                const buttons = parent.querySelectorAll('.accordion-button');
                const containers = parent.querySelectorAll('.accordion-collapse');
                if(expand) {
                    buttons.forEach(btn => btn.classList.remove('collapsed'));
                    containers.forEach(btn => btn.classList.add('show'));
                } else {
                    buttons.forEach(btn => btn.classList.add('collapsed'));
                    containers.forEach(btn => btn.classList.remove('show'));
                }
            };
            const attributeGroupItemCount = (items, onlyVisible = true) => {
                if(!onlyVisible) {
                    return items.length;
                } else {
                    return items.filter(itm => !state.selectedEntityTypeAttributeIds.includes(itm.id)).length;
                }
            };
            const attributeGroupHasItems = items => {
                if(state.showHiddenAttributes && items.length > 0) return true;

                return attributeGroupItemCount(items) > 0;
            };

            // DATA
            const accordionRef = ref(null);
            const state = reactive({
                siu: computed(_ => store.getters.datatypeDataOf('si-unit')),
                systemAttributeList: computed(_ => store.getters.attributes.filter(a => a.is_system)),
                userAttributeList: computed(_ => store.getters.attributes.filter(a => !a.is_system)),
                attributeList: computed(_ => {
                    if(!state.sortAttributes) return state.filteredAttributeList;

                    return state.filteredAttributeList.toSorted((a, b) => {
                        const conceptA = translateConcept(a.thesaurus_url);
                        const conceptB = translateConcept(b.thesaurus_url);
                        return conceptA.localeCompare(conceptB);
                    });
                }),
                filteredAttributeList: computed(_ => {
                    if(!state.attributeQuery) return state.userAttributeList;

                    return state.userAttributeList.filter(attribute => {
                        return translateConcept(attribute.thesaurus_url).toLowerCase().indexOf(state.attributeQuery.toLowerCase()) > -1;
                    });
                }),
                // set values for all attributes to '', so values in <attribute-list> are existant
                attributeListValues: computed(_ => {
                    if(!state.attributeList) return;
                    let data = {};
                    for(let i=0; i<state.attributeList.length; i++) {
                        let a = state.attributeList[i];
                        data[a.id] = {
                            value: getInitialAttributeValue(a, 'datatype'),
                        };
                    }
                    return data;
                }),
                attributeListGroups: computed(_ => {
                    const grps = {};
                    state.attributeList.forEach(a => {
                        if(!grps[a.datatype]) {
                            grps[a.datatype] = [];
                        }
                        grps[a.datatype].push(a);
                    });
                    return grps;
                }),
                sortedAttributeListGroups: computed(_ => {
                    const groupList = Object.entries(state.attributeListGroups);

                    return groupList.sort((a, b) => {
                        const labelA = t(`global.attributes.${a[0]}`);
                        const labelB = t(`global.attributes.${b[0]}`);
                        return labelA.localeCompare(labelB);
                    });
                }),
                showHiddenAttributes: false,
                showAttributesInGroups: true,
                sortAttributes: true,
                attributeQuery: '',
                entityTypes: computed(_ => Object.values(store.getters.entityTypes)),
                selectedEntityType: computed(_ => parseInt(currentRoute.params.id)),
                selectedEntityTypeAttributeIds: computed(_ => state.selectedEntityType ? getEntityTypeAttributes(state.selectedEntityType).map(a => a.id) : []),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                setEntityType,
                addEntityType,
                duplicateEntityType,
                editEntityType,
                requestDeleteEntityType,
                createAttribute,
                onDeleteAttribute,
                setAttributeGroupExpand,
                attributeGroupItemCount,
                attributeGroupHasItems,
                // STATE
                accordionRef,
                state,
            };
        },
    };
</script>
