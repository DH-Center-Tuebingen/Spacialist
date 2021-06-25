<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-dcan="'duplicate_edit_concepts|view_concept_props'">
        <div class="col-md-2 py-2 h-100 d-flex flex-column bg-light-dark">
            <h4>{{ t('main.datamodel.entity.title') }}</h4>
            <entity-type-list
                class="col px-0 h-100 d-flex flex-column overflow-hidden"
                :data="state.entityTypes"
                :selected-id="state.selectedEntityType"
                @add-element="addEntityType"
                @delete-element="requestDeleteEntityType"
                @duplicate-element="duplicateEntityType"
                @edit-element="editEntityType"
                @select-element="setEntityType">
            </entity-type-list>
        </div>
        <div class="col-md-6 py-2 h-100 bg-light-dark rounded-end ">
            <router-view>
            </router-view>
        </div>
        <div class="col-md-4 py-2 h-100 d-flex flex-column">
            <div class="d-flex flex-row justify-content-between">
                <h4>
                    {{ t('main.datamodel.attribute.title') }}
                    <button type="button" class="btn btn-outline-success" @click="createAttribute()">
                        <i class="fas fa-fw fa-plus"></i> {{ t('main.datamodel.attribute.add_button') }}
                    </button>
                </h4>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggle-hidden-attributes" v-model="state.showHiddenAttributes">
                    <label class="form-check-label" for="toggle-hidden-attributes">
                        {{ t('main.datamodel.attribute.show_hidden') }}
                    </label>
                </div>
            </div>
            <div class="col overflow-hidden mt-2">
                <attribute-list
                    class="h-100 scroll-y-auto scroll-x-hidden"
                    group="attribute-selection"
                    :attributes="state.attributeList"
                    :hidden-attributes="state.selectedEntityTypeAttributeIds"
                    :show-hidden="state.showHiddenAttributes"
                    :values="state.attributeListValues"
                    :selections="{}"
                    :is-source="true"
                    :show-info="true"
                    @delete-element="onDeleteAttribute">
                </attribute-list>
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

    import store from '../bootstrap/store.js';
    import router from '../bootstrap/router.js';

    import {
        duplicateEntityType as duplicateEntityTypeApi,
        getEntityTypeOccurrenceCount,
        getAttributeOccurrenceCount,
    } from '../api.js';

    import {
        getEntityTypeAttributes,
    } from '../helpers/helpers.js';

    import {
        showAddEntityType,
        showDeleteEntityType,
        showAddAttribute,
        showDeleteAttribute,
        showEditEntityType,
    } from '../helpers/modal.js';

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
                })
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

            // DATA
            const state = reactive({
                attributeList: computed(_ => store.getters.attributes),
                // set values for all attributes to '', so values in <attribute-list> are existant
                attributeListValues: computed(_ => {
                    if(!state.attributeList) return;
                    let data = {};
                    for(let i=0; i<state.attributeList.length; i++) {
                        let a = state.attributeList[i];
                        data[a.id] = '';
                    }
                    return data;
                }),
                showHiddenAttributes: false,
                entityTypes: computed(_ => Object.values(store.getters.entityTypes)),
                selectedEntityType: computed(_ => currentRoute.params.id),
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
                // STATE
                state,
            }
        },
    }
</script>
