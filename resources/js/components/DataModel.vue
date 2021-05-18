<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-dcan="'duplicate_edit_concepts|view_concept_props'">
        <div class="col-md-2 py-2 h-100 d-flex flex-column bg-light-dark">
            <h4>{{ t('main.datamodel.entity.title') }}</h4>
            <entity-type-list
                class="col px-0 h-100 d-flex flex-column overflow-hidden"
                :data="state.entityTypes"
                :selected-id="state.selectedEntityType"
                @add="addEntityType"
                @delete="requestDeleteEntityType"
                @duplicate="duplicateEntityType"
                @edit="onEditEntityType"
                @select="setEntityType">
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
                        <i class="fas fa-fw fa-plus"></i> {{ t('main.datamodel.attribute.add-button') }}
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
                requestDeleteEntityType,
                createAttribute,
                onDeleteAttribute,
                // STATE
                state,
            }
        },
        // methods: {
        //     attributeFromCreateEvent(event) {
        //         const attribute = event.attribute;
        //         let data = {};
        //         data.label_id = attribute.label.concept_id;
        //         data.datatype = attribute.type.datatype;
        //         data.recursive = attribute.recursive;
        //         if(this.needsColumns) {
        //             data.columns = JSON.stringify(this.columns);
        //         }
        //         if(attribute.root) {
        //             data.root_id = attribute.root.concept_id;
        //         } else if(attribute.root_id) {
        //             data.root_attribute_id = attribute.root_id;
        //         }
        //         if(attribute.textContent) {
        //             data.text = attribute.textContent;
        //         }
        //         return data;
        //     },
        //     createAttribute(event) {
        //         if(!this.validated) return;
        //         const data = this.attributeFromCreateEvent(event);
        //         $httpQueue.add(() => $http.post('/editor/dm/attribute', data).then(response => {
        //             this.attributeList.push(response.data);
        //             this.hideNewAttributeModal();
        //         }));
        //     },
        //     checkAttributeType(event) {
        //         this.needsColumns = event.type == 'table';
        //     },
        //     checkValidation(event) {
        //         this.validated = event.state;
        //     },
        //     addColumn(event) {
        //         const column = this.attributeFromCreateEvent(event);
        //         this.columns.push(column);
        //     },
        //     deleteAttribute(attribute) {
        //         const id = attribute.id;
        //         $httpQueue.add(() => $http.delete(`/editor/dm/attribute/${id}`).then(response => {
        //             let index = this.attributeList.findIndex(function(a) {
        //                 return a.id == id;
        //             });
        //             if(index) {
        //                 let delAttr = this.attributeList.splice(index, 1);
        //                 if(delAttr.length) {
        //                     delAttr = delAttr[0];
        //                     this.$showToast(
        //                         this.$t('main.datamodel.toasts.attribute_deleted.title'),
        //                         this.$t('main.datamodel.toasts.attribute_deleted.msg', {
        //                             name: this.$translateConcept(delAttr.thesaurus_url)
        //                         }),
        //                         'success'
        //                     );
        //                 }
        //             }
        //             this.hideDeleteAttributeModal();
        //         }));
        //     },
        //     createEntityType(entityType) {
        //         if(this.newEntityTypeDisabled) return;
        //         const url = entityType.label.concept.concept_url;
        //         let data = {
        //             'concept_url': url,
        //             'is_root': entityType.is_root || false,
        //             'geomtype': entityType.geomtype.key
        //         };
        //         $httpQueue.add(() => $http.post('/editor/dm/entity_type', data).then(response => {
        //             this.$addEntityType(response.data);
        //             this.localEntityTypes = Object.values(this.$getEntityTypes());
        //             this.hideNewEntityTypeModal();
        //         }));
        //     },
        //     deleteEntityType(entityType) {
        //         const vm = this;
        //         const id = entityType.id;
        //         $httpQueue.add(() => vm.$http.delete('/editor/dm/entity_type/' + id).then(function(response) {
        //             const index = vm.localEntityTypes.findIndex(function(ct) {
        //                 return ct.id == id;
        //             });
        //             if(index) {
        //                 vm.localEntityTypes.splice(index, 1);
        //             }
        //             vm.hideDeleteEntityTypeModal();
        //         }));
        //     },
        //     onDeleteAttribute(attribute) {
        //         const vm = this;
        //         const id = attribute.id;
        //         $httpQueue.add(() => vm.$http.get('/editor/dm/attribute/occurrence_count/'+id).then(function(response) {
        //             vm.setAttributeValueCount(response.data);
        //             vm.setModalSelectedAttribute(attribute);
        //             vm.openedModal = 'delete-attribute-modal';
        //             vm.$modal.show('delete-attribute-modal');
        //         }));
        //     },
        //     hideNewAttributeModal() {
        //         this.attributeTypes = [];
        //         this.columns = [];
        //         this.needsColumns = false;
        //         this.$modal.hide('new-attribute-modal');
        //     },
        //     hideDeleteAttributeModal() {
        //         this.$modal.hide('delete-attribute-modal');
        //         this.openedModal = '';
        //         this.attributeValueCount = 0;
        //     },
        //     onCreateEntityType() {
        //         const vm = this;
        //         $httpQueue.add(() => vm.$http.get('/editor/dm/geometry').then(function(response) {
        //             vm.availableGeometries = [];
        //             let idCtr = 1;
        //             response.data.forEach(g => {
        //                 const geom = {
        //                     id: idCtr,
        //                     label: g,
        //                     key: g
        //                 };
        //                 vm.availableGeometries.push(geom);
        //                 idCtr++;
        //             });
        //             vm.availableGeometries.push({
        //                 id: idCtr,
        //                 label: 'Any',
        //                 key: 'any'
        //             });
        //             vm.$modal.show('new-entity-type-modal');
        //         }));
        //     },
        //     onEditEntityType(event) {
        //         const entityType = event.type;
        //         this.$modal.show(EditEntityTypeModal, {
        //             entityType: {...entityType},
        //             onSubmit: editedType => this.editEntityType(editedType, entityType)
        //         });
        //     },
        //     editEntityType(edited, original) {
        //         const id = edited.id;
        //         const data = {
        //             label: edited.thesaurus_url
        //         };
        //         $httpQueue.add(() => $http.patch(`/editor/dm/entity_type/${id}/label`, data).then(response => {
        //             original.thesaurus_url = edited.thesaurus_url;
        //         }));
        //     },
        //     hideNewEntityTypeModal() {
        //         this.newEntityType = {};
        //         this.$modal.hide('new-entity-type-modal');
        //     },
        //     hideDeleteEntityTypeModal() {
        //         this.$modal.hide('delete-entity-type-modal');
        //         this.entityCount = 0;
        //         this.openedModal = '';
        //     },
        //     newEntityTypeSearchResultSelected(label) {
        //         Vue.set(this.newEntityType, 'label', label);
        //     },
        //     setEntityType(event) {
        //         const entityType = event.type;
        //         this.$router.push({
        //             name: 'dmdetail',
        //             params: {
        //                 id: entityType.id
        //             }
        //         });
        //     },
        //     //
        //     setAttributeValueCount(cnt) {
        //         this.attributeValueCount = cnt;
        //     },
        //     setModalSelectedAttribute(attribute) {
        //         this.modalSelectedAttribute = Object.assign({}, attribute);
        //     },
        //     setEntityCount(cnt) {
        //         this.entityCount = cnt;
        //     },
        //     setModalSelectedEntityType(entityType) {
        //         this.modalSelectedEntityType = Object.assign({}, entityType);
        //     }
        // },
        // data() {
        //     return {
        //         attributeList: [],
        //         initFinished: false,
        //         entityType: {},
        //         entityAttributes: [],
        //         entitySelections: {},
        //         entityDependencies: {},
        //         entityValues: {},
        //         attributeTypes: [],
        //         columnAttributeTypes: [],
        //         columns: [],
        //         needsColumns: false,
        //         validated: false,
        //         openedModal: '',
        //         modalSelectedAttribute: {},
        //         attributeValueCount: 0,
        //         availableGeometries: [],
        //         newEntityType: {},
        //         localEntityTypes: Object.values(this.$getEntityTypes()),
        //         modalSelectedEntityType: {},
        //         entityCount: 0,
        //         allowedTableKeys: [
        //             'string', 'string-sc', 'integer', 'double', 'boolean', 'iconclass', 'entity'
        //         ]
        //     }
        // },
    }
</script>
