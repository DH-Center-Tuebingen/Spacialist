<template>
    <div class="row d-flex flex-row of-hidden h-100" v-can="'duplicate_edit_concepts|view_concept_props'">
        <div class="col-md-5 h-100 d-flex flex-column">
            <h4>{{ $t('main.datamodel.attribute.title') }}</h4>
            <button type="button" class="btn btn-success mb-2" @click="onCreateAttribute">
                <i class="fas fa-fw fa-plus"></i> {{ $t('main.datamodel.attribute.add-button') }}
            </button>
            <attributes
                class="col scroll-y-auto"
                group="attributes"
                :attributes="attributeList"
                :values="attributeListValues"
                :selections="{}"
                :is-source="true"
                :on-delete="onDeleteAttribute"
                :show-info="true">
            </attributes>
        </div>
        <div class="col-md-2 d-flex flex-column">
            <h4>{{ $t('main.datamodel.entity.title') }}</h4>
            <entity-types
                class="col px-0 h-100 scroll-y-auto"
                :data="localEntityTypes"
                @add="onCreateEntityType"
                @delete="onDeleteEntityType"
                @duplicate="onDuplicateEntityType"
                @edit="onEditEntityType"
                @select="setEntityType">
            </entity-types>
        </div>
        <router-view class="col-md-5 h-100"
            :attributes="attributeList">
        </router-view>

        <modals-container class="visible-overflow" />

        <modal name="new-entity-type-modal" height="auto" :scrollable="true" classes="of-visible">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('main.datamodel.entity.modal.new.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewEntityTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newEntityTypeForm" id="newEntityTypeForm" role="form" v-on:submit.prevent="createEntityType(newEntityType)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                {{ $t('global.label') }}:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="newEntityTypeSearchResultSelected"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="isRoot" v-model="newEntityType.is_root" />
                            <label class="form-check-label" for="isRoot">
                                {{ $t('main.datamodel.detail.properties.top-level') }}
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                {{ $t('global.geometry-type') }}:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    label="label"
                                    track-by="id"
                                    v-model="newEntityType.geomtype"
                                    :allowEmpty="false"
                                    :closeOnSelect="true"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="availableGeometries"
                                    :placeholder="$t('global.select.placehoder')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')">
                                </multiselect>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newEntityTypeForm" :disabled="newEntityTypeDisabled">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewEntityTypeModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="new-attribute-modal" height="auto" :scrollable="true" classes="of-visible">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('main.datamodel.attribute.modal.new.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <create-attribute
                        :attribute-types="attributeTypes"
                        :external-submit="newAttributeFormId"
                        v-on:created="createAttribute"
                        v-on:selected-type="checkAttributeType"
                        v-on:validation="checkValidation"
                    >
                    </create-attribute>
                    <div v-if="needsColumns">
                        <h5>
                            {{ $tc('global.column', 2) }}
                            <span class="badge">
                                {{ columns.length }}
                            </span>
                        </h5>
                        <create-attribute
                            :attribute-types="columnAttributeTypes"
                            v-on:created="addColumn"
                        >
                        </create-attribute>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" :form="newAttributeFormId" :disabled="!validated">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewAttributeModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-entity-type-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'delete-entity-type-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete-name.title', {name: $translateConcept(modalSelectedEntityType.thesaurus_url)}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteEntityTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete-name.desc', {name: $translateConcept(modalSelectedEntityType.thesaurus_url)}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('main.datamodel.entity.modal.delete.alert', entityCount, {
                                name: $translateConcept(modalSelectedEntityType.thesaurus_url),
                                cnt: entityCount
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteEntityType(modalSelectedEntityType)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteEntityTypeModal">
                        <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-attribute-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'delete-attribute-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete-name.title', {name: $translateConcept(modalSelectedAttribute.thesaurus_url)}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete-name.desc', {name: $translateConcept(modalSelectedAttribute.thesaurus_url)}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('main.datamodel.attribute.modal.delete.alert', attributeValueCount, {
                                name: $translateConcept(modalSelectedAttribute.thesaurus_url),
                                cnt: attributeValueCount
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteAttribute(modalSelectedAttribute)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteAttributeModal">
                        <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import CreateAttribute from './CreateAttribute.vue';
    import EditEntityTypeModal from './modals/EditEntityType.vue';

    export default {
        beforeRouteEnter(to, from, next) {
            $httpQueue.add(() => $http.get('editor/dm/attribute').then(response => {
                next(vm => vm.init(response.data));
            }));
        },
        components: {
            CreateAttribute
        },
        mounted() {},
        methods: {
            init(attributes) {
                this.initFinished = false;
                this.attributeList = attributes;
                this.initFinished = true;
            },
            attributeFromCreateEvent(event) {
                const attribute = event.attribute;
                let data = {};
                data.label_id = attribute.label.concept.id;
                data.datatype = attribute.type.datatype;
                data.recursive = attribute.recursive;
                if(this.needsColumns) {
                    data.columns = JSON.stringify(this.columns);
                }
                if(attribute.root) {
                    data.root_id = attribute.root.concept.id;
                }
                if(attribute.textContent) {
                    data.text = attribute.textContent;
                }
                return data;
            },
            createAttribute(event) {
                if(!this.validated) return;
                const data = this.attributeFromCreateEvent(event);
                $httpQueue.add(() => $http.post('/editor/dm/attribute', data).then(response => {
                    this.attributeList.push(response.data);
                    this.hideNewAttributeModal();
                }));
            },
            checkAttributeType(event) {
                this.needsColumns = event.type == 'table';
            },
            checkValidation(event) {
                this.validated = event.state;
            },
            addColumn(event) {
                const column = this.attributeFromCreateEvent(event);
                this.columns.push(column);
            },
            deleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                $httpQueue.add(() => vm.$http.delete(`/editor/dm/attribute/${id}`).then(function(response) {
                    let index = vm.attributeList.findIndex(function(a) {
                        return a.id == id;
                    });
                    if(index) {
                        vm.attributeList.splice(index, 1);
                    }
                    vm.hideDeleteAttributeModal();
                }));
            },
            createEntityType(entityType) {
                const vm = this;
                if(vm.newEntityTypeDisabled) return;
                const url = entityType.label.concept.concept_url;
                let data = {
                    'concept_url': url,
                    'is_root': entityType.is_root || false,
                    'geomtype': entityType.geomtype.key
                };
                $httpQueue.add(() => vm.$http.post('/editor/dm/entity_type', data).then(function(response) {
                    vm.localEntityTypes.push(response.data);
                    vm.hideNewEntityTypeModal();
                }));
            },
            deleteEntityType(entityType) {
                const vm = this;
                const id = entityType.id;
                $httpQueue.add(() => vm.$http.delete('/editor/dm/entity_type/' + id).then(function(response) {
                    const index = vm.localEntityTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        vm.localEntityTypes.splice(index, 1);
                    }
                    vm.hideDeleteEntityTypeModal();
                }));
            },
            onCreateAttribute() {
                $httpQueue.add(() => $http.get('/editor/dm/attribute_types').then(response => {
                    this.attributeTypes = [];
                    for(let i=0; i<response.data.length; i++) {
                        const at = response.data[i];
                        this.attributeTypes.push(at);
                        if(this.allowedTableKeys.indexOf(at.datatype) >= 0) {
                            this.columnAttributeTypes.push(at);
                        }
                    }
                    this.$modal.show('new-attribute-modal');
                }));
            },
            onDeleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                $httpQueue.add(() => vm.$http.get('/editor/dm/attribute/occurrence_count/'+id).then(function(response) {
                    vm.setAttributeValueCount(response.data);
                    vm.setModalSelectedAttribute(attribute);
                    vm.openedModal = 'delete-attribute-modal';
                    vm.$modal.show('delete-attribute-modal');
                }));
            },
            hideNewAttributeModal() {
                this.attributeTypes = [];
                this.columns = [];
                this.needsColumns = false;
                this.$modal.hide('new-attribute-modal');
            },
            hideDeleteAttributeModal() {
                this.$modal.hide('delete-attribute-modal');
                this.openedModal = '';
                this.attributeValueCount = 0;
            },
            onCreateEntityType() {
                const vm = this;
                $httpQueue.add(() => vm.$http.get('/editor/dm/geometry').then(function(response) {
                    vm.availableGeometries = [];
                    let idCtr = 1;
                    response.data.forEach(g => {
                        const geom = {
                            id: idCtr,
                            label: g,
                            key: g
                        };
                        vm.availableGeometries.push(geom);
                        idCtr++;
                    });
                    vm.availableGeometries.push({
                        id: idCtr,
                        label: 'Any',
                        key: 'any'
                    });
                    vm.$modal.show('new-entity-type-modal');
                }));
            },
            onDeleteEntityType(event) {
                const entityType = event.type;
                const vm = this;
                const id = entityType.id;
                $httpQueue.add(() => vm.$http.get('/editor/dm/entity_type/occurrence_count/' + id).then(function(response) {
                    vm.setEntityCount(response.data);
                    vm.setModalSelectedEntityType(entityType);
                    vm.openedModal = 'delete-entity-type-modal';
                    vm.$modal.show('delete-entity-type-modal');
                }));
            },
            onDuplicateEntityType(event) {
                const id = event.id;
                $httpQueue.add(() => $http.post(`/editor/dm/entity_type/${id}/duplicate`).then(response => {
                    this.localEntityTypes.push(response.data);
                    this.$addEntityType(response.data);
                }));
            },
            onEditEntityType(event) {
                const entityType = event.type;
                this.$modal.show(EditEntityTypeModal, {
                    entityType: {...entityType},
                    onSubmit: editedType => this.editEntityType(editedType, entityType)
                });
            },
            editEntityType(edited, original) {
                const id = edited.id;
                const data = {
                    label: edited.thesaurus_url
                };
                $httpQueue.add(() => $http.patch(`/editor/dm/entity_type/${id}/label`, data).then(response => {
                    original.thesaurus_url = edited.thesaurus_url;
                }));
            },
            hideNewEntityTypeModal() {
                this.$modal.hide('new-entity-type-modal');
            },
            hideDeleteEntityTypeModal() {
                this.$modal.hide('delete-entity-type-modal');
                this.entityCount = 0;
                this.openedModal = '';
            },
            newEntityTypeSearchResultSelected(label) {
                Vue.set(this.newEntityType, 'label', label);
            },
            setEntityType(event) {
                const entityType = event.type;
                this.$router.push({
                    name: 'dmdetail',
                    params: {
                        id: entityType.id
                    }
                });
            },
            //
            setAttributeValueCount(cnt) {
                this.attributeValueCount = cnt;
            },
            setModalSelectedAttribute(attribute) {
                this.modalSelectedAttribute = Object.assign({}, attribute);
            },
            setEntityCount(cnt) {
                this.entityCount = cnt;
            },
            setModalSelectedEntityType(entityType) {
                this.modalSelectedEntityType = Object.assign({}, entityType);
            }
        },
        data() {
            return {
                attributeList: [],
                initFinished: false,
                entityType: {},
                entityAttributes: [],
                entitySelections: {},
                entityDependencies: {},
                entityValues: {},
                newAttributeFormId: 'new-attribute-form-external-submit',
                attributeTypes: [],
                columnAttributeTypes: [],
                columns: [],
                needsColumns: false,
                validated: false,
                openedModal: '',
                modalSelectedAttribute: {},
                attributeValueCount: 0,
                availableGeometries: [],
                newEntityType: {},
                localEntityTypes: Object.values(this.$getEntityTypes()),
                modalSelectedEntityType: {},
                entityCount: 0,
                allowedTableKeys: [
                    'string', 'string-sc', 'integer', 'double', 'boolean'
                ]
            }
        },
        computed: {
            // set values for all attributes to '', so values in <attributes> are existant
            attributeListValues: function() {
                let data = {};
                for(let i=0; i<this.attributeList.length; i++) {
                    let a = this.attributeList[i];
                    data[a.id] = '';
                }
                return data;
            },
            newEntityTypeDisabled: function() {
                const nct = this.newEntityType;
                return !nct.label || !nct.geomtype;
            }
        }
    }
</script>
