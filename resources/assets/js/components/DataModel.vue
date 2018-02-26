<template>
    <div class="row d-flex flex-row of-hidden col">
        <div class="col-md-5 h-100 scroll-x-auto">
            <h4>Available Attributes</h4>
            <button type="button" class="btn btn-success" v-on:click="onCreateAttribute">
                <i class="fas fa-fw fa-plus"></i> Add Attribute
            </button>
            <attributes
                :attributes="attributes"
                :values="values"
                :concepts="concepts"
                :on-delete="deleteAttribute"
                :show-info="true">
            </attributes>
        </div>
        <div class="col-md-2 h-100 scroll-x-auto">
            <h4>Available Context-Types</h4>
            <context-types
                :data="localContextTypes"
                :concepts="concepts"
                :on-add="onCreateContextType"
                :on-delete="onDeleteContextType">
            </context-types>
        </div>
        <div class="col-md-5 h-100 scroll-x-auto">
            <h4>Added Attributes</h4>
            <button type="button" class="btn btn-success" v-on:click="addAttributeToContextType">
                <i class="fas fa-fw fa-plug"></i> Add Attribute to <code>Name</code>.
            </button>
            <attributes
                :attributes="attributes"
                :values="values"
                :concepts="concepts"
                :on-delete="deleteAttribute"
                :on-reorder="reorderContextAttribute"
                :show-info="true">
            </attributes>
        </div>

        <modal name="new-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Context-Type</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideNewContextTypeModal">
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
                    <button type="button" class="btn btn-danger" v-on:click="hideNewContextTypeModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-context-type-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="selectedContextType.thesaurus_url">
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ concepts[selectedContextType.thesaurus_url].label }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteContextTypeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to delete Context-Type <i>{{ concepts[selectedContextType.thesaurus_url].label }}</i>?
                    </p>
                    <p class="alert alert-danger">
                        Please note: If you delete <i>{{ concepts[selectedContextType.thesaurus_url].label }}</i>, {{ contextCount }} contexts of this type are deleted as well.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteContextType(selectedContextType)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteContextTypeModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: ['attributes', 'values', 'concepts', 'contextTypes'],
        mounted() {},
        methods: {
            deleteAttribute(list, position) {

            },
            createContextType(contextType) {
                if(!contextType.label) return;
                let contextTypes = this.localContextTypes;
                let url = contextType.label.concept.concept_url;
                let data = {
                    'concept_url': url
                };
                axios.post('/api/editor/dm/context_type', data).then(function(response) {
                    contextTypes.push(response.data);
                    this.hideNewContextTypeModal();
                });
            },
            deleteContextType(contextType) {
                let id = contextType.id;
                let contextTypes = this.localContextTypes;
                let hideDeleteContextTypeModal = this.hideDeleteContextTypeModal;
                axios.delete('/api/editor/dm/context_type/' + id).then(function(response) {
                    let index = contextTypes.findIndex(function(ct) {
                        return ct.id == id;
                    });
                    if(index) {
                        contextTypes.splice(index, 1);
                    }
                    hideDeleteContextTypeModal();
                });
            },
            addAttributeToContextType(list, position, attribute) {

            },
            removeAttributeFromContextType(list, position) {

            },
            reorderContextAttribute(list, oldPosition, newPosition) {

            },
            // Modal Methods
            onCreateAttribute() {
                this.$modal.show('new-attribute-modal');
            },
            hideNewAttributeModal() {
                this.$modal.hide('new-attribute-modal');
            },
            onCreateContextType() {
                this.$modal.show('new-context-type-modal');
            },
            onDeleteContextType(contextType) {
                let id = contextType.id;
                let setContextCount = this.setContextCount;
                let setSelectedContextType = this.setSelectedContextType;
                let modal = this.$modal;
                axios.get('/api/editor/dm/occurrence_count/' + id).then(function(response) {
                    setContextCount(response.data);
                    setSelectedContextType(contextType);
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
            newContextTypeSearchResultSelected(label) {
                Vue.set(this.newContextType, 'label', label);
            },
            //
            setContextCount(cnt) {
                this.contextCount = cnt;
            },
            setSelectedContextType(contextType) {
                this.selectedContextType = Object.assign({}, contextType);
            }
        },
        data() {
            return {
                newContextType: {},
                localContextTypes: this.contextTypes.slice(),
                selectedContextType: {},
                contextCount: 0
            }
        }
    }
</script>
