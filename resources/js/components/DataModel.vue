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
                <div
                    v-if="state.showAttributesInGroups"
                    id="dme-attribute-list-accordion"
                    class="accordion accordion-flush pe-2 col overflow-y-auto overflow-x-hidden"
                >
                    <template
                        v-for="(attrGrp, type) in state.attributeListGroups"
                        :key="`dme-attribute-list-${type}-grp`"
                    >
                        <div
                            v-show="attributeGroupHasItems(attrGrp)"
                            class="accordion-item"
                        >
                            <h2 class="accordion-header">
                                <button
                                    class="accordion-button collapsed"
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
                                class="accordion-collapse collapse"
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
                <attribute-list
                    v-else
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
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
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
            const state = reactive({
                siu: computed(_ => store.getters.datatypeDataOf('si-unit')),
                systemAttributeList: computed(_ => store.getters.attributes.filter(a => a.is_system)),
                attributeList: computed(_ => store.getters.attributes.filter(a => !a.is_system)),
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
                showHiddenAttributes: false,
                showAttributesInGroups: false,
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
                attributeGroupItemCount,
                attributeGroupHasItems,
                // STATE
                state,
            };
        },
    };
</script>
