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
            <context-types
                class="col px-0 h-100 scroll-y-auto"
                :data="localContextTypes"
                :on-add="onCreateContextType"
                :on-delete="onDeleteContextType"
                :on-select="setContextType">
            </context-types>
        </div>
        <router-view class="col-md-5 h-100"
            :attributes="attributeList">
        </router-view>

        <modal name="new-context-type-modal" height="auto" :scrollable="true" classes="of-visible">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('main.datamodel.entity.modal.new.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewContextTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newContextTypeForm" id="newContextTypeForm" role="form" v-on:submit.prevent="createContextType(newContextType)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                {{ $t('global.label') }}:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="newContextTypeSearchResultSelected"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="isRoot" v-model="newContextType.is_root" />
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
                                    v-model="newContextType.geomtype"
                                    :allowEmpty="false"
                                    :closeOnSelect="false"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="availableGeometries"
                                    :placeholder="$t('global.select.select')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')">
                                </multiselect>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newContextTypeForm" :disabled="newContextTypeDisabled">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewContextTypeModal">
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
                                    track-by="id"
                                    v-model="newAttribute.type"
                                    :allowEmpty="false"
                                    :closeOnSelect="true"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="attributeTypes"
                                    :placeholder="$t('global.select.select')"
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

        <modal name="delete-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'delete-context-type-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete-name.title', {name: $translateConcept(modalSelectedContextType.thesaurus_url)}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteContextTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete-name.desc', {name: $translateConcept(modalSelectedContextType.thesaurus_url)}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('main.datamodel.entity.modal.delete.alert', contextCount, {
                                name: $translateConcept(modalSelectedContextType.thesaurus_url),
                                cnt: contextCount
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteContextType(modalSelectedContextType)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteContextTypeModal">
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
            createContextType(contextType) {
                const vm = this;
                if(vm.newContextTypeDisabled) return;
                const url = contextType.label.concept.concept_url;
                let data = {
                    'concept_url': url,
                    'is_root': contextType.is_root || false,
                    'geomtype': contextType.geomtype.key
                };
                vm.$http.post('/editor/dm/context_type', data).then(function(response) {
                    vm.localContextTypes.push(response.data);
                    vm.hideNewContextTypeModal();
                });
            },
            deleteContextType(contextType) {
                const vm = this;
                const id = contextType.id;
                vm.$http.delete('/editor/dm/context_type/' + id).then(function(response) {
                    const index = vm.localContextTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        vm.localContextTypes.splice(index, 1);
                    }
                    vm.hideDeleteContextTypeModal();
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
            onCreateContextType() {
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
                    vm.$modal.show('new-context-type-modal');
                });
            },
            onDeleteContextType(contextType) {
                const vm = this;
                const id = contextType.id;
                vm.$http.get('/editor/dm/context_type/occurrence_count/' + id).then(function(response) {
                    vm.setContextCount(response.data);
                    vm.setModalSelectedContextType(contextType);
                    vm.openedModal = 'delete-context-type-modal';
                    vm.$modal.show('delete-context-type-modal');
                });
            },
            hideNewContextTypeModal() {
                this.$modal.hide('new-context-type-modal');
            },
            hideDeleteContextTypeModal() {
                this.$modal.hide('delete-context-type-modal');
                this.contextCount = 0;
                this.openedModal = '';
            },
            setAttributeLabel(label) {
                Vue.set(this.newAttribute, 'label', label);
            },
            setAttributeRoot(label) {
                Vue.set(this.newAttribute, 'root', label);
            },
            newContextTypeSearchResultSelected(label) {
                Vue.set(this.newContextType, 'label', label);
            },
            setContextType(contextType) {
                this.$router.push({
                    name: 'dmdetail',
                    params: {
                        id: contextType.id
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
            setContextCount(cnt) {
                this.contextCount = cnt;
            },
            setModalSelectedContextType(contextType) {
                this.modalSelectedContextType = Object.assign({}, contextType);
            }
        },
        data() {
            return {
                attributeList: [],
                initFinished: false,
                contextType: {},
                contextAttributes: [],
                contextSelections: {},
                contextDependencies: {},
                contextValues: {},
                attributeTypes: [],
                newAttribute: {},
                openedModal: '',
                modalSelectedAttribute: {},
                attributeValueCount: 0,
                availableGeometries: [],
                newContextType: {},
                localContextTypes: Object.values(this.$getEntityTypes()),
                modalSelectedContextType: {},
                contextCount: 0,
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
            newContextTypeDisabled: function() {
                const nct = this.newContextType;
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
