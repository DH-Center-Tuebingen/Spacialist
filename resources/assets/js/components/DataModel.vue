<template>
    <div class="row d-flex flex-row of-hidden col">
        <div class="col-md-5 h-100 scroll-x-auto">
            <h4>Available Attributes</h4>
            <button type="button" class="btn btn-success" @click="onCreateAttribute">
                <i class="fas fa-fw fa-plus"></i> Add Attribute
            </button>
            <attributes
                group="attributes"
                :attributes="localAttributes"
                :values="{}"
                :concepts="concepts"
                :is-source="true"
                :on-delete="onDeleteAttribute"
                :show-info="true">
            </attributes>
        </div>
        <div class="col-md-2 h-100 scroll-x-auto">
            <h4>Available Context-Types</h4>
            <context-types
                :data="localContextTypes"
                :concepts="concepts"
                :on-add="onCreateContextType"
                :on-delete="onDeleteContextType"
                :on-select="setContextType">
            </context-types>
        </div>
        <div class="col-md-5 h-100 scroll-x-auto">
            <h4>Added Attributes</h4>
            <attributes
                group="attributes"
                :attributes="contextAttributes"
                :values="{}"
                :concepts="concepts"
                :on-add="addAttributeToContextType"
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
                    <form name="newContextTypeForm" class="form-horizontal" role="form" v-on:submit.prevent="createContextType(newContextType)">
                        <div class="form-group">
                            <label class="control-label col-md-3" for="name">
                                Label:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="newContextTypeSearchResultSelected"
                                ></label-search>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
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
                    <form name="newContextTypeForm" class="form-horizontal" role="form" v-on:submit.prevent="createAttribute(newAttribute)">
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                Label:
                            </label>
                            <div class="col-md-9">
                                <label-search
                                    :on-select="newAttributeSearchResultSelected"
                                ></label-search>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3">
                                Type:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    label="datatype"
                                    v-model="newAttribute.type"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="attributeTypes">
                                </multiselect>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="hideNewAttributeModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="modalSelectedContextType.thesaurus_url">
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

        <modal name="delete-attribute-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="modalSelectedAttribute.thesaurus_url">
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
            <div class="modal-content" v-if="modalSelectedAttribute.thesaurus_url">
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
        props: ['attributes', 'values', 'concepts', 'contextTypes'],
        mounted() {},
        methods: {
            createAttribute(attribute) {
                if(!attribute.label) return;
                if(!attribute.type) return;
                let attributes = this.localAttributes;
                let hideModal = this.hideNewAttributeModal;
                let data = {};
                data.label_id = attribute.label.concept.id;
                data.datatype = attribute.type.datatype;
                if(data.datatype == 'table') {
                    data.columns = JSON.stringify(attribute.columns);
                }
                if(attribute.parent) {
                    data.parent_id = attribute.parent.id;
                }
                this.$http.post('/api/editor/dm/attribute', data).then(function(response) {
                    attributes.push(response.data);
                    hideModal();
                });
            },
            deleteAttribute(attribute) {
                let id = attribute.id;
                let attributes = this.localAttributes;
                let hideModal = this.hideDeleteAttributeModal;
                this.$http.delete('/api/editor/dm/attribute/' + id).then(function(response) {
                    let index = attributes.findIndex(function(a) {
                        return a.id == id;
                    });
                    if(index) {
                        attributes.splice(index, 1);
                    }
                    hideModal();
                });
            },
            createContextType(contextType) {
                if(!contextType.label) return;
                let contextTypes = this.localContextTypes;
                let hideModal = this.hideNewContextTypeModal;
                let url = contextType.label.concept.concept_url;
                let data = {
                    'concept_url': url
                };
                this.$http.post('/api/editor/dm/context_type', data).then(function(response) {
                    contextTypes.push(response.data);
                    hideModal();
                });
            },
            deleteContextType(contextType) {
                let id = contextType.id;
                let contextTypes = this.localContextTypes;
                let hideModal = this.hideDeleteContextTypeModal;
                this.$http.delete('/api/editor/dm/context_type/' + id).then(function(response) {
                    let index = contextTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        contextTypes.splice(index, 1);
                    }
                    hideModal();
                });
            },
            addAttributeToContextType(oldIndex, index) {
                let ctid = this.contextType.id;
                let attribute = this.localAttributes[oldIndex];
                let attributes = this.contextAttributes;
                let data = {};
                data.attribute_id = attribute.id;
                data.position = index + 1;
                this.$http.post('/api/editor/dm/context_type/'+ctid+'/attribute', data).then(function(response) {
                    // Add element to attribute list
                    attributes.splice(index, 0, response.data);
                    // Update position attribute of successors
                    for(let i=index+1; i<attributes.length; i++) {
                        attributes[i].position++;
                    }
                })

            },
            removeAttributeFromContextType(attribute) {
                let ctid = this.contextType.id;
                let aid = attribute.id;
                let attributes = this.contextAttributes;
                let hideModal = this.hideRemoveAttributeModal;
                this.$http.delete('/api/editor/dm/context_type/'+ctid+'/attribute/'+aid).then(function(response) {
                    let index = attributes.findIndex(function(a) {
                        return a.id == attribute.id;
                    });
                    if(index > -1) {
                        // Remove element from attribute list
                        attributes.splice(index, 1);
                        // Update position attribute of successors
                        for(let i=index; i<attributes.length; i++) {
                            attributes[i].position--;
                        }
                    }
                    hideModal();
                });
            },
            reorderContextAttribute(oldIndex, index) {
                let attributes = this.contextAttributes;
                let attribute = attributes[oldIndex];
                let ctid = this.contextType.id;
                let aid = attribute.id;
                let position = index + 1;
                // same index, nothing to do
                if(oldIndex == index) {
                    return;
                }
                let data = {};
                data.position = position;
                this.$http.patch('/api/editor/dm/context_type/'+ctid+'/attribute/'+aid+'/position', data).then(function(response) {
                    attribute.position = position;
                    attributes.splice(oldIndex, 1);
                    attributes.splice(index, 0, attribute);
                    if(oldIndex < index) {
                        for(let i=oldIndex; i<index; i++) {
                            attributes[i].position--;
                        }
                    } else { // oldIndex > index
                        for(let i=index+1; i<=oldIndex; i++) {
                            attributes[i].position++;
                        }
                    }
                });
            },
            // Modal Methods
            onRemoveAttributeFromContextType(attribute) {
                let setModalSelectedAttribute = this.setModalSelectedAttribute;
                let setModalSelectedContextType = this.setModalSelectedContextType;
                let setAttributeValueCount = this.setAttributeValueCount;
                let contextType = this.contextType;
                let modal = this.$modal;
                let aid = attribute.id;
                let ctid = contextType.id;
                this.$http.get('/api/editor/dm/attribute/occurrence_count/'+aid+'/'+ctid).then(function(response) {
                    setModalSelectedAttribute(attribute);
                    setModalSelectedContextType(contextType);
                    setAttributeValueCount(response.data);
                    modal.show('remove-attribute-from-ct-modal');
                });
            },
            hideRemoveAttributeModal() {
                this.$modal.hide('remove-attribute-from-ct-modal');
            },
            onCreateAttribute() {
                let aT = this.attributeTypes;
                let modal = this.$modal;
                this.$http.get('/api/editor/dm/attribute_types').then(function(response) {
                    for(let i=0; i<response.data.length; i++) {
                        aT.push(response.data[i]);
                    }
                    modal.show('new-attribute-modal');
                });
            },
            onDeleteAttribute(attribute) {
                let id = attribute.id;
                let setAttributeValueCount = this.setAttributeValueCount;
                let setModalSelectedAttribute = this.setModalSelectedAttribute;
                let modal = this.$modal;
                this.$http.get('/api/editor/dm/attribute/occurrence_count/'+id).then(function(response) {
                    setAttributeValueCount(response.data);
                    setModalSelectedAttribute(attribute);
                    modal.show('delete-attribute-modal');
                });
            },
            hideNewAttributeModal() {
                this.$modal.hide('new-attribute-modal');
            },
            hideDeleteAttributeModal() {
                this.$modal.hide('delete-attribute-modal');
                this.attributeValueCount = 0;
            },
            onCreateContextType() {
                this.$modal.show('new-context-type-modal');
            },
            onDeleteContextType(contextType) {
                let id = contextType.id;
                let setContextCount = this.setContextCount;
                let setModalSelectedContextType = this.setModalSelectedContextType;
                let modal = this.$modal;
                this.$http.get('/api/editor/dm/context_type/occurrence_count/' + id).then(function(response) {
                    setContextCount(response.data);
                    setModalSelectedContextType(contextType);
                    modal.show('delete-context-type-modal');
                });
            },
            hideNewContextTypeModal() {
                this.$modal.hide('new-context-type-modal');
            },
            hideDeleteContextTypeModal() {
                this.$modal.hide('delete-context-type-modal');
                this.contextCount = 0;
            },
            newAttributeSearchResultSelected(label) {
                Vue.set(this.newAttribute, 'label', label);
            },
            newContextTypeSearchResultSelected(label) {
                Vue.set(this.newContextType, 'label', label);
            },
            setContextType(contextType) {
                this.contextAttributes = [];
                this.contextType = Object.assign({}, contextType);
                let id = contextType.id;
                let attrs = this.contextAttributes;
                this.$http.get('/api/editor/context_type/'+id+'/attribute')
                    .then(function(response) {
                        for(let i=0; i<response.data.length; i++) {
                            attrs.push(response.data[i]);
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
                contextType: {},
                contextAttributes: [],
                attributeTypes: [],
                newAttribute: {},
                modalSelectedAttribute: {},
                attributeValueCount: 0,
                localAttributes: this.attributes.slice(),
                newContextType: {},
                localContextTypes: this.contextTypes.slice(),
                modalSelectedContextType: {},
                contextCount: 0,
                allowedTableKeys: [
                    'string', 'string-sc', 'integer', 'double', 'boolean'
                ],
                hasParent: {
                    'string-sc': 1,
                    'string-mc': 1,
                    epoch: 1
                }
            }
        }
    }
</script>
