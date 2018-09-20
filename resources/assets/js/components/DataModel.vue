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
                :on-add="onCreateEntityType"
                :on-delete="onDeleteEntityType"
                :on-select="setEntityType">
            </entity-types>
        </div>
        <router-view class="col-md-5 h-100"
            :attributes="attributeList">
        </router-view>

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
                    <form id="newAttributeForm" name="newAttributeForm" role="form" v-on:submit.prevent="createAttribute(newAttribute)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.label') }}:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="setAttributeLabel"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.type') }}:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    label="datatype"
                                    track-by="datatype"
                                    v-model="newAttribute.type"
                                    :allowEmpty="false"
                                    :closeOnSelect="true"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="attributeTypes"
                                    :placeholder="$t('global.select.placehoder')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')">
                                </multiselect>
                            </div>
                        </div>
                        <div class="form-group" v-show="needsRootElement">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.root-element') }}:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="setAttributeRoot"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-group" v-show="needsTextElement">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.content') }}:
                            </label>
                            <div class="col-md-9">
                                <textarea class="form-control" v-model="newAttribute.textContent"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newAttributeForm" :disabled="!validated">
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
    export default {
        beforeRouteEnter(to, from, next) {
            $http.get('editor/dm/attribute').then(response => {
                next(vm => vm.init(response.data));
            });
        },
        mounted() {},
        methods: {
            init(attributes) {
                this.initFinished = false;
                this.attributeList = attributes;
                this.initFinished = true;
            },
            createAttribute(attribute) {
                const vm = this;
                if(!vm.validated) return;
                let data = {};
                data.label_id = attribute.label.concept.id;
                data.datatype = attribute.type.datatype;
                if(data.datatype == 'table') {
                    data.columns = JSON.stringify(attribute.columns);
                }
                if(vm.needsRootElement) {
                    data.root_id = attribute.root.concept.id;
                }
                if(vm.needsTextElement) {
                    data.text = attribute.textContent;
                }
                vm.$http.post('/editor/dm/attribute', data).then(function(response) {
                    vm.attributeList.push(response.data);
                    vm.hideNewAttributeModal();
                });
            },
            deleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                vm.$http.delete(`/editor/dm/attribute/${id}`).then(function(response) {
                    let index = vm.attributeList.findIndex(function(a) {
                        return a.id == id;
                    });
                    if(index) {
                        vm.attributeList.splice(index, 1);
                    }
                    vm.hideDeleteAttributeModal();
                });
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
                vm.$http.post('/editor/dm/entity_type', data).then(function(response) {
                    vm.localEntityTypes.push(response.data);
                    vm.hideNewEntityTypeModal();
                });
            },
            deleteEntityType(entityType) {
                const vm = this;
                const id = entityType.id;
                vm.$http.delete('/editor/dm/entity_type/' + id).then(function(response) {
                    const index = vm.localEntityTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        vm.localEntityTypes.splice(index, 1);
                    }
                    vm.hideDeleteEntityTypeModal();
                });
            },
            onCreateAttribute() {
                const vm = this;
                vm.$http.get('/editor/dm/attribute_types').then(function(response) {
                    for(let i=0; i<response.data.length; i++) {
                        vm.attributeTypes.push(response.data[i]);
                    }
                    vm.$modal.show('new-attribute-modal');
                });
            },
            onDeleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                vm.$http.get('/editor/dm/attribute/occurrence_count/'+id).then(function(response) {
                    vm.setAttributeValueCount(response.data);
                    vm.setModalSelectedAttribute(attribute);
                    vm.openedModal = 'delete-attribute-modal';
                    vm.$modal.show('delete-attribute-modal');
                });
            },
            hideNewAttributeModal() {
                this.newAttribute = {};
                this.attributeTypes = [];
                this.$modal.hide('new-attribute-modal');
            },
            hideDeleteAttributeModal() {
                this.$modal.hide('delete-attribute-modal');
                this.openedModal = '';
                this.attributeValueCount = 0;
            },
            onCreateEntityType() {
                const vm = this;
                vm.$http.get('/editor/dm/geometry').then(function(response) {
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
                });
            },
            onDeleteEntityType(entityType) {
                const vm = this;
                const id = entityType.id;
                vm.$http.get('/editor/dm/entity_type/occurrence_count/' + id).then(function(response) {
                    vm.setEntityCount(response.data);
                    vm.setModalSelectedEntityType(entityType);
                    vm.openedModal = 'delete-entity-type-modal';
                    vm.$modal.show('delete-entity-type-modal');
                });
            },
            hideNewEntityTypeModal() {
                this.$modal.hide('new-entity-type-modal');
            },
            hideDeleteEntityTypeModal() {
                this.$modal.hide('delete-entity-type-modal');
                this.entityCount = 0;
                this.openedModal = '';
            },
            setAttributeLabel(label) {
                Vue.set(this.newAttribute, 'label', label);
            },
            setAttributeRoot(label) {
                Vue.set(this.newAttribute, 'root', label);
            },
            newEntityTypeSearchResultSelected(label) {
                Vue.set(this.newEntityType, 'label', label);
            },
            setEntityType(entityType) {
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
                attributeTypes: [],
                newAttribute: {},
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
            needsRootElement: function() {
                return this.newAttribute.type &&
                    (
                        this.newAttribute.type.datatype == 'string-sc' ||
                        this.newAttribute.type.datatype == 'string-mc' ||
                        this.newAttribute.type.datatype == 'epoch'
                    );
            },
            needsTextElement: function() {
                return this.newAttribute.type &&
                    (
                        this.newAttribute.type.datatype == 'sql'
                    );
            },
            newEntityTypeDisabled: function() {
                const nct = this.newEntityType;
                return !nct.label || !nct.geomtype;
            },
            validated: function() {
                let isValid = this.newAttribute.label &&
                    this.newAttribute.type &&
                    this.newAttribute.label.id > 0 &&
                    this.newAttribute.type.datatype.length > 0 &&
                    (
                        !this.needsRootElement ||
                        (
                            this.needsRootElement &&
                            this.newAttribute.root &&
                            this.newAttribute.root.id > 0
                        )
                    ) &&
                    (
                        !this.needsTextElement ||
                        (
                            this.needsTextElement &&
                            this.newAttribute.textContent &&
                            this.newAttribute.textContent.length > 0
                        )
                    );
                return isValid;
            }
        }
    }
</script>
