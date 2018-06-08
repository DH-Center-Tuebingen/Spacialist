<template>
    <div class="row d-flex flex-row of-hidden col h-100">
        <div class="col-md-5 h-100 d-flex flex-column">
            <h4>Available Attributes</h4>
            <button type="button" class="btn btn-success mb-2" @click="onCreateAttribute">
                <i class="fas fa-fw fa-plus"></i> Add Attribute
            </button>
            <attributes
                class="col scroll-y-auto"
                group="attributes"
                :attributes="localAttributes"
                :values="localAttributeValues"
                :selections="{}"
                :concepts="concepts"
                :is-source="true"
                :on-delete="onDeleteAttribute"
                :show-info="true">
            </attributes>
        </div>
        <div class="col-md-2 d-flex flex-column">
            <h4>Available Context-Types</h4>
            <context-types
                class="col px-0 h-100 scroll-y-auto"
                :data="localContextTypes"
                :concepts="concepts"
                :on-add="onCreateContextType"
                :on-delete="onDeleteContextType"
                :on-select="setContextType">
            </context-types>
        </div>
        <div class="col-md-5 h-100 d-flex flex-column">
            <h4>Properties</h4>
            <form role="form" v-on:submit.prevent="updateContextType" v-if="contextType.id">
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right">Top-Level Context-Type</label>
                    <div class="col-md-9">
                        <input type="checkbox" v-model="contextType.is_root" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right">Allowed Sub Context-Types</label>
                    <div class="col-md-9">
                        <multiselect
                            label="thesaurus_url"
                            track-by="id"
                            v-model="contextType.sub_context_types"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :customLabel="translateLabel"
                            :hideSelected="true"
                            :multiple="true"
                            :options="minimalContextTypes">
                        </multiselect>
                        <div class="pt-2">
                            <button type="button" class="btn btn-outline-success mr-2" @click="addAllContextTypes">
                                <i class="fas fa-fw fa-tasks"></i> Select all
                            </button>
                            <button type="button" class="btn btn-outline-danger" @click="removeAllContextTypes">
                                <i class="fas fa-fw fa-times"></i> Deselect all
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-save"></i> Save
                        </button>
                    </div>
                </div>
                <hr />
            </form>
            <h4>Added Attributes</h4>
            <attributes
                class="col scroll-y-auto"
                group="attributes"
                :attributes="contextAttributes"
                :values="contextValues"
                :selections="contextSelections"
                :concepts="concepts"
                :on-add="addAttributeToContextType"
                :on-edit="onEditContextAttribute"
                :on-remove="onRemoveAttributeFromContextType"
                :on-reorder="reorderContextAttribute"
                :show-info="true">
            </attributes>
        </div>

        <modal name="new-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Context-Type</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewContextTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newContextTypeForm" id="newContextTypeForm" role="form" v-on:submit.prevent="createContextType(newContextType)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="name">
                                Label:
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
                                Top-Level Context-Type?
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newContextTypeForm">
                        <i class="fas fa-fw fa-plus"></i> Add
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewContextTypeModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="new-attribute-modal" height="auto" :scrollable="true" classes="of-visible">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Attribute</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newAttributeForm" name="newAttributeForm" role="form" v-on:submit.prevent="createAttribute(newAttribute)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3">
                                Label:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="setAttributeLabel"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label col-md-3">
                                Type:
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
                                    :options="attributeTypes">
                                </multiselect>
                            </div>
                        </div>
                        <div class="form-group" v-show="needsRootElement">
                            <label class="col-form-label col-md-3">
                                Root Element:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="setAttributeRoot"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-group" v-show="needsTextElement">
                            <label class="col-form-label col-md-3">
                                Content:
                            </label>
                            <div class="col-md-9">
                                <textarea class="form-control" v-model="newAttribute.textContent"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newAttributeForm" :disabled="!validated">
                        <i class="fas fa-fw fa-plus"></i> Add
                    </button>
                    <button type="button" class="btn btn-danger" @click="hideNewAttributeModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'delete-context-type-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ concepts[modalSelectedContextType.thesaurus_url].label }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteContextTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to delete Context-Type <i>{{ concepts[modalSelectedContextType.thesaurus_url].label }}</i>?
                    </p>
                    <p class="alert alert-danger">
                        Please note: If you delete <i>{{ concepts[modalSelectedContextType.thesaurus_url].label }}</i>, {{ contextCount }} contexts of this type are deleted as well.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteContextType(modalSelectedContextType)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteContextTypeModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="edit-context-attribute-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'edit-context-attribute-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{ concepts[modalSelectedAttribute.thesaurus_url].label }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideEditContextAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editContextAttributeForm" name="editContextAttributeForm" role="form" v-on:submit.prevent="editContextAttribute(modalSelectedAttribute, selectedDependency)">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                Label:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="concepts[modalSelectedAttribute.thesaurus_url].label" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                Type:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="modalSelectedAttribute.datatype" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                Depends On:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    class="mb-2"
                                    label="thesaurus_url"
                                    track-by="id"
                                    v-model="selectedDependency.attribute"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :customLabel="translateLabel"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="depends.attributes"
                                    @input="dependencyAttributeSelected">
                                </multiselect>
                                <multiselect
                                    class="mb-2"
                                    label="id"
                                    track-by="id"
                                    v-if="selectedDependency.attribute && selectedDependency.attribute.id"
                                    v-model="selectedDependency.operator"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="dependencyOperators">
                                </multiselect>
                                <div v-if="selectedDependency.attribute && selectedDependency.attribute.id">
                                    <input type="checkbox" v-if="dependencyType == 'boolean'" v-model="selectedDependency.value" />
                                    <input type="number" step="1" v-if="dependencyType == 'integer'" v-model="selectedDependency.value" />
                                    <input type="number" step="0.01" v-if="dependencyType == 'double'" v-model="selectedDependency.value" />
                                    <input type="text" v-if="dependencyType == 'string'" v-model="selectedDependency.value" />
                                    <multiselect
                                    label="concept_url"
                                    track-by="id"
                                    v-if="dependencyType == 'select'"
                                    v-model="selectedDependency.value"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :customLabel="translateLabel"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="depends.values">
                                </multiselect>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="editContextAttributeForm" class="btn btn-success" :disabled="editContextAttributeDisabled">
                        <i class="fas fa-fw fa-save"></i> Update
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideEditContextAttributeModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-attribute-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'delete-attribute-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ concepts[modalSelectedAttribute.thesaurus_url].label }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to delete Attribute <i>{{ concepts[modalSelectedAttribute.thesaurus_url].label }}</i>?
                    </p>
                    <p class="alert alert-danger">
                        Please note: If you delete <i>{{ concepts[modalSelectedAttribute.thesaurus_url].label }}</i>, {{ attributeValueCount }} values of this attribute in the contexts are deleted as well.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteAttribute(modalSelectedAttribute)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteAttributeModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="remove-attribute-from-ct-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'remove-attribute-from-ct-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">Remove {{ concepts[modalSelectedAttribute.thesaurus_url].label }} from {{ concepts[modalSelectedContextType.thesaurus_url].label }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideRemoveAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to remove Attribute <i>{{ concepts[modalSelectedAttribute.thesaurus_url].label }}</i> from Context-Type <i>{{ concepts[modalSelectedContextType.thesaurus_url].label }}</i>?
                    </p>
                    <p class="alert alert-danger">
                        Please note: If you delete <i>{{ concepts[modalSelectedAttribute.thesaurus_url].label }}</i>, {{ attributeValueCount }} values of this attribute in the contexts of type <i>{{ concepts[modalSelectedContextType.thesaurus_url].label }}</i> are deleted as well.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="removeAttributeFromContextType(modalSelectedAttribute)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideRemoveAttributeModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: ['attributes', 'concepts', 'contextTypes', 'values'],
        mounted() {},
        methods: {
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
                vm.$http.post('/api/editor/dm/attribute', data).then(function(response) {
                    vm.localAttributes.push(response.data);
                    vm.hideNewAttributeModal();
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            deleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                vm.$http.delete(`/api/editor/dm/attribute/${id}`).then(function(response) {
                    let index = vm.localAttributes.findIndex(function(a) {
                        return a.id == id;
                    });
                    if(index) {
                        vm.localAttributes.splice(index, 1);
                    }
                    vm.hideDeleteAttributeModal();
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            createContextType(contextType) {
                const vm = this;
                if(!contextType.label) return;
                const url = contextType.label.concept.concept_url;
                let data = {
                    'concept_url': url,
                    'is_root': contextType.is_root || false
                };
                vm.$http.post('/api/editor/dm/context_type', data).then(function(response) {
                    vm.localContextTypes.push(response.data);
                    vm.hideNewContextTypeModal();
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            deleteContextType(contextType) {
                const vm = this;
                const id = contextType.id;
                vm.$http.delete('/api/editor/dm/context_type/' + id).then(function(response) {
                    const index = vm.localContextTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        vm.localContextTypes.splice(index, 1);
                    }
                    vm.hideDeleteContextTypeModal();
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            addAllContextTypes() {
                this.contextType.sub_context_types = [];
                this.contextType.sub_context_types = this.minimalContextTypes.slice();
            },
            removeAllContextTypes() {
                this.contextType.sub_context_types = [];
            },
            updateContextType() {
                const vm = this;
                if(!vm.contextType.id) return;
                const id = vm.contextType.id;
                const data = {
                    'is_root': vm.contextType.is_root,
                    'sub_context_types': vm.contextType.sub_context_types.map(t => t.id)
                };
                vm.$http.post('/api/editor/dm/'+id+'/relation', data).then(function(response) {
                    const name = vm.$translateConcept(vm.concepts, vm.contextType.thesaurus_url);
                    vm.$showToast('Entity-Type updated', `${name} successfully updated.`, 'success');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            addAttributeToContextType(oldIndex, index) {
                const vm = this;
                const ctid = vm.contextType.id;
                const attribute = vm.localAttributes[oldIndex];
                let attributes = vm.contextAttributes;
                let data = {};
                data.attribute_id = attribute.id;
                data.position = index + 1;
                vm.$http.post(`/api/editor/dm/context_type/${ctid}/attribute`, data).then(function(response) {
                    // Add element to attribute list
                    attributes.splice(index, 0, response.data);
                    Vue.set(vm.contextValues, response.data.id, '');
                    // Update position attribute of successors
                    for(let i=index+1; i<attributes.length; i++) {
                        attributes[i].position++;
                    }
                    const attrName = vm.$translateConcept(vm.concepts, response.data.thesaurus_url);
                    const etName = vm.$translateConcept(vm.concepts, vm.contextType.thesaurus_url);
                    vm.$showToast('Attribute added', `${attrName} successfully added to ${etName}.`, 'success');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });

            },
            editContextAttribute(attribute, options) {
                const vm = this;
                if(vm.editContextAttributeDisabled) return;
                const aid = attribute.id;
                const ctid = attribute.context_type_id;
                let data = {
                    d_attribute: options.attribute.id,
                    d_operator: options.operator.id
                };
                data.d_value = vm.getDependencyValue(options.value, options.attribute.datatype);
                vm.$http.patch(`/api/editor/dm/context_type/${ctid}/attribute/${aid}/dependency`, data).then(function(response) {

                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            removeAttributeFromContextType(attribute) {
                const vm = this;
                const ctid = vm.contextType.id;
                const aid = attribute.id;
                vm.$http.delete('/api/editor/dm/context_type/'+ctid+'/attribute/'+aid).then(function(response) {
                    const index = vm.contextAttributes.findIndex(function(a) {
                        return a.id == attribute.id;
                    });
                    if(index > -1) {
                        // Remove element from attribute list
                        vm.contextAttributes.splice(index, 1);
                        // Update position attribute of successors
                        for(let i=index; i<vm.contextAttributes.length; i++) {
                            vm.contextAttributes[i].position--;
                        }
                    }
                    vm.hideRemoveAttributeModal();
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            reorderContextAttribute(oldIndex, index) {
                const vm = this;
                let attribute = vm.contextAttributes[oldIndex];
                const ctid = vm.contextType.id;
                let aid = attribute.id;
                let position = index + 1;
                // same index, nothing to do
                if(oldIndex == index) {
                    return;
                }
                let data = {};
                data.position = position;
                vm.$http.patch(`/api/editor/dm/context_type/${ctid}/attribute/${aid}/position`, data).then(function(response) {
                    attribute.position = position;
                    vm.contextAttributes.splice(oldIndex, 1);
                    vm.contextAttributes.splice(index, 0, attribute);
                    if(oldIndex < index) {
                        for(let i=oldIndex; i<index; i++) {
                            vm.contextAttributes[i].position--;
                        }
                    } else { // oldIndex > index
                        for(let i=index+1; i<=oldIndex; i++) {
                            vm.contextAttributes[i].position++;
                        }
                    }
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            // Modal Methods
            onRemoveAttributeFromContextType(attribute) {
                const vm = this;
                const aid = attribute.id;
                const ctid = vm.contextType.id;
                vm.$http.get(`/api/editor/dm/attribute/occurrence_count/${aid}/${ctid}`).then(function(response) {
                    vm.setModalSelectedAttribute(attribute);
                    vm.setModalSelectedContextType(vm.contextType);
                    vm.setAttributeValueCount(response.data);
                    vm.openedModal = 'remove-attribute-from-ct-modal';
                    vm.$modal.show('remove-attribute-from-ct-modal');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            hideRemoveAttributeModal() {
                this.$modal.hide('remove-attribute-from-ct-modal');
                this.openedModal = '';
            },
            onCreateAttribute() {
                const vm = this;
                vm.$http.get('/api/editor/dm/attribute_types').then(function(response) {
                    for(let i=0; i<response.data.length; i++) {
                        vm.attributeTypes.push(response.data[i]);
                    }
                    vm.$modal.show('new-attribute-modal');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
                });
            },
            onDeleteAttribute(attribute) {
                const vm = this;
                const id = attribute.id;
                vm.$http.get('/api/editor/dm/attribute/occurrence_count/'+id).then(function(response) {
                    vm.setAttributeValueCount(response.data);
                    vm.setModalSelectedAttribute(attribute);
                    vm.openedModal = 'delete-attribute-modal';
                    vm.$modal.show('delete-attribute-modal');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
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
            onEditContextAttribute(attribute) {
                const vm = this;
                const ctid = vm.contextType.id;
                vm.depends.attributes = vm.contextAttributes.filter(function(a) {
                    return a.id != attribute.id;
                });
                vm.setModalSelectedAttribute(attribute);
                vm.setSelectedDependency(attribute.depends_on);
                vm.openedModal = 'edit-context-attribute-modal';
                vm.$modal.show('edit-context-attribute-modal');
            },
            hideEditContextAttributeModal() {
                this.$modal.hide('edit-context-attribute-modal');
                this.openedModal = '';
                this.selectedDependency.attribute = {};
                this.selectedDependency.operator = undefined;
                this.selectedDependency.value = undefined;
            },
            dependencyAttributeSelected(attribute) {
                const vm = this;
                if(!attribute) {
                    vm.depends.values = [];
                    return;
                }
                const id = attribute.id;
                switch(attribute.datatype) {
                    case 'string-sc':
                    case 'string-mc':
                        vm.$http.get(`/api/editor/attribute/${id}/selection`).then(function(response) {
                            vm.depends.values = [];
                            const selections = response.data;
                            if(selections) {
                                for(let i=0; i<selections.length; i++) {
                                    vm.depends.values.push(selections[i]);
                                }
                            }
                        }).catch(function(error) {
                            if(error.response) {
                                const r = error.response;
                                vm.$showErrorModal(r.data, r.status, r.headers);
                            } else if(error.request) {
                                vm.$showErrorModal(error.request);
                            } else {
                                vm.$showErrorModal(error.message);
                            }
                        });
                        break;
                    default:
                        vm.depends.values = [];
                        break;
                }
            },
            getDependencyValue(valObject, type) {
                switch(type) {
                    case 'string-sc':
                    case 'string-mc':
                        return valObject.concept_url;
                    default:
                        return valObject;
                }
            },
            setSelectedDependency(values) {
                if(!values) return;
                let aid;
                // We have an object with only one key
                // Hacky way to get that key
                for(let k in values) {
                    aid = k;
                    break;
                }
                this.selectedDependency.attribute = this.contextAttributes.find(function(a) {
                    return a.id == aid;
                });
                this.selectedDependency.operator = {id: values[aid].operator};
                if(this.selectedDependency.attribute) {
                    switch(this.selectedDependency.attribute.datatype) {
                        case 'string-sc':
                        case 'string-mc':
                            this.selectedDependency.value = {
                                concept_url: values[aid].value
                            };
                            break;
                        default:
                            this.selectedDependency.value = values[aid].value;
                            break;
                    }
                }
                console.log(this.selectedDependency);
            },
            onCreateContextType() {
                this.$modal.show('new-context-type-modal');
            },
            onDeleteContextType(contextType) {
                const vm = this;
                const id = contextType.id;
                vm.$http.get('/api/editor/dm/context_type/occurrence_count/' + id).then(function(response) {
                    vm.setContextCount(response.data);
                    vm.setModalSelectedContextType(contextType);
                    vm.openedModal = 'delete-context-type-modal';
                    vm.$modal.show('delete-context-type-modal');
                }).catch(function(error) {
                    if(error.response) {
                        const r = error.response;
                        vm.$showErrorModal(r.data, r.status, r.headers);
                    } else if(error.request) {
                        vm.$showErrorModal(error.request);
                    } else {
                        vm.$showErrorModal(error.message);
                    }
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
                const vm = this;
                vm.contextAttributes = [];
                vm.contextType = Object.assign({}, contextType);
                let id = contextType.id;
                vm.$http.get('/api/editor/context_type/'+id+'/attribute')
                    .then(function(response) {
                        let data = response.data;
                        // if result is empty, php returns [] instead of {}
                        if(data.selections instanceof Array) {
                            data.selections = {};
                        }
                        vm.contextSelections = data.selections;
                        for(let i=0; i<data.attributes.length; i++) {
                            vm.contextAttributes.push(data.attributes[i]);
                            // Set values for all context attributes to '', so values in <attributes> are existant
                            Vue.set(vm.contextValues, data.attributes[i].id, '');
                        }
                        for(let i=0; i<vm.localAttributes.length; i++) {
                            let id = vm.localAttributes[i].id;
                            let index = vm.contextAttributes.findIndex(a => a.id == id);
                            vm.localAttributes[i].isDisabled = index > -1;
                        }
                    }).catch(function(error) {
                        if(error.response) {
                            const r = error.response;
                            vm.$showErrorModal(r.data, r.status, r.headers);
                        } else if(error.request) {
                            vm.$showErrorModal(error.request);
                        } else {
                            vm.$showErrorModal(error.message);
                        }
                    });
            },
            translateLabel(element, label) {
                let value = element[label];
                if(!value) return element;
                let concept = this.concepts[element[label]];
                if(!concept) return element;
                return concept.label;
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
                contextType: {},
                contextAttributes: [],
                contextSelections: {},
                contextValues: {},
                attributeTypes: [],
                newAttribute: {},
                selectedDependency: {
                    attribute: {},
                    operator: undefined,
                    value: undefined
                },
                depends: {
                    attributes: [],
                    values: []
                },
                openedModal: '',
                modalSelectedAttribute: {},
                attributeValueCount: 0,
                localAttributes: this.attributes.slice(),
                newContextType: {},
                localContextTypes: this.contextTypes.slice(),
                modalSelectedContextType: {},
                contextCount: 0,
                allowedTableKeys: [
                    'string', 'string-sc', 'integer', 'double', 'boolean'
                ]
            }
        },
        computed: {
            dependencyOperators: function() {
                if(!this.selectedDependency.attribute) return [];
                switch(this.selectedDependency.attribute.datatype) {
                    case 'boolean':
                        return [
                            {id: '='}
                        ];
                    case 'double':
                    case 'integer':
                    case 'date':
                    case 'percentage':
                        return [
                            {id: '<'},
                            {id: '>'},
                            {id: '='},
                        ];
                    default:
                        return [
                            {id: '='}
                        ];
                }
            },
            dependencyType: function() {
                if(!this.selectedDependency.attribute) return '';
                switch(this.selectedDependency.attribute.datatype) {
                    case 'boolean':
                        return 'boolean';
                    case 'double':
                        return 'double';
                    case 'integer':
                    case 'date':
                    case 'percentage':
                        return 'integer';
                    case 'string-sc':
                    case 'string-mc':
                        return 'select';
                    default:
                        return 'string';
                }
            },
            // set values for all attributes to '', so values in <attributes> are existant
            localAttributeValues: function() {
                let data = {};
                for(let i=0; i<this.localAttributes.length; i++) {
                    let a = this.localAttributes[i];
                    data[a.id] = '';
                }
                return data;
            },
            minimalContextTypes: function() {
                return this.localContextTypes.map(ct => ({
                    id: ct.id,
                    thesaurus_url: ct.thesaurus_url
                }));
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
            editContextAttributeDisabled: function() {
                return !this.modalSelectedAttribute ||
                    // Either all or none of the deps must be set to be valid
                       !(
                           (
                               this.selectedDependency.attribute &&
                               this.selectedDependency.attribute.id &&
                               this.selectedDependency.operator &&
                               this.selectedDependency.operator.id &&
                               this.selectedDependency.value
                            )
                            ||
                            (
                                (
                                    !this.selectedDependency.attribute ||
                                    !this.selectedDependency.attribute.id
                                ) &&
                               (
                                   !this.selectedDependency.operator ||
                                   !this.selectedDependency.operator.id
                               ) &&
                               !this.selectedDependency.value
                            )
                        )
                        ;
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
