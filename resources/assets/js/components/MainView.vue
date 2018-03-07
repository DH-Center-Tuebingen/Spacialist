<template>
    <div class="row">
        <div class="col-md-2" id="tree-container">
            <div>
                <h3>Contexts</h3>
                <div class="col-md-12">
                    <form>
                        <div class="form-group row">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input class="form-control" type="search" placeholder="Search" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-sm btn-outline-success ml-3 mb-2" @click="requestAddNewEntity">
                        <i class="fas fa-fw fa-plus"></i> Add new Top-Level Entity
                    </button>
                    <context-tree
                        :concepts="concepts"
                        :context-types="contextTypes"
                        :on-context-menu-add="requestAddNewEntity"
                        :on-context-menu-duplicate="duplicateEntity"
                        :on-context-menu-delete="requestDeleteEntity"
                        :roots="tree"
                        :selection-callback="setSelectedElement">
                    </context-tree>
                    <button type="button" class="btn btn-sm btn-outline-success ml-3 mt-2" @click="requestAddNewEntity">
                        <i class="fas fa-fw fa-plus"></i> Add new Top-Level Entity
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-5" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" >
            <div v-if="selectedContext.id">
                <div class="d-flex align-items-center justify-content-between">
                    <h1>{{ selectedContext.name }}</h1>
                    <span>
                        <button type="button" class="btn btn-success" @click="saveEntity(selectedContext)">
                            <i class="fas fa-fw fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-danger" @click="requestDeleteEntity(selectedContext)">
                            <i class="fas fa-fw fa-trash"></i> Delete
                        </button>
                    </span>
                </div>
                <attributes v-if="isLoaded"
                    :attributes="selectedContext.attributes"
                    :concepts="concepts"
                    :on-metadata="showMetadata"
                    :metadata-addon="hasReferenceGroup"
                    :selections="selectedContext.selections"
                    :values="selectedContext.data">
                </attributes>
            </div>
            <h1 v-else>Nothing selected</h1>
        </div>
        <div class="col-md-5" id="addon-container">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'map'}" v-on:click="tab = 'map'">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Map
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'files'}" v-on:click="tab = 'files'">
                        <i class="fas fa-fw fa-folder"></i> Files
                    </a>
                </li>
                <li class="nav-item" v-if="selectedContext.id">
                    <a class="nav-link" href="#" v-bind:class="{active: tab == 'references'}" v-on:click="tab = 'references'">
                        <i class="fas fa-fw fa-bookmark"></i> References
                    </a>
                </li>
            </ul>
            <div class="mt-2">
                <ol-map v-if="tab == 'map'"></ol-map>
                <div v-if="tab == 'files'">
                    <h2>Files loaded</h2>
                </div>
                <div v-if="tab == 'references'">
                    <p class="alert alert-info" v-if="!hasReferences">
                        No references found.
                    </p>
                    <div v-if="hasReferences" v-for="(referenceGroup, key) in selectedContext.references">
                        <h5><a href="#">{{ concepts[key].label }}</a></h5>
                        <div class="list-group">
                            <a class="list-group-item list-group-item-action" v-for="reference in referenceGroup">
                                <h6>{{ reference.bibliography.title }}</h6>
                                <p class="mb-0">
                                    {{ reference.bibliography.author }} <span class="text-lightgray">{{ reference.bibliography.year}}</span>
                                </p>
                                <p class="font-weight-light font-italic mb-0">
                                    {{ reference.description }}
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <modal name="add-entity-modal" height="auto" :scrollable="true" classes="of-visible">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add new Entity</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideNewEntityModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="newEntityForm" class="form-horizontal" role="form" v-on:submit.prevent="addNewEntity(newEntity)">
                        <div class="form-group row">
                            <label class="control-label col-md-3" for="name">
                                Name:
                            </label>
                            <div class="col-md-9">
                                <input type="text" id="name" class="form-control" required v-model="newEntity.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="control-label col-md-3" for="type">
                                Type:
                            </label>
                            <multiselect class="col-md-9" style="box-sizing: border-box;"
                                :customLabel="translateLabel"
                                label="thesaurus_url"
                                v-model="newEntity.type"
                                :allowEmpty="false"
                                :closeOnSelect="true"
                                :hideSelected="true"
                                :multiple="false"
                                :options="newEntity.selection">
                            </multiselect>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="hideNewEntityModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-entity-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ toDeleteEntity.name }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteEntityModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to delete Entity <i>{{ toDeleteEntity.name }}</i>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteEntity(toDeleteEntity)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDeleteEntityModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: {
            concepts: {
                required: false, // TODO required?
                type: Object
            },
            contextTypes: {
                required: false, // TODO required?
                type: Object
            },
            tree: {
                required: true,
                type: Array
            }
        },
        mounted() {},
        methods: {
            setSelectedElement(element) {
                this.attributesLoaded = false;
                this.dataLoaded = false;
                if(!element) {
                    this.selectedContext = {};
                } else {
                    this.selectedContext = Object.assign({}, element);
                    this.getContextData(element);
                }
            },
            getContextData(elem) {
                let ctx = this.selectedContext;
                let cid = elem.id;
                let ctid = elem.context_type_id;
                let vm = this;
                vm.$http.get('/api/context/'+cid+'/data').then(function(response) {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(ctx, 'data', response.data);
                    Vue.set(vm, 'dataLoaded', true);
                    return vm.$http.get('/api/editor/context_type/'+ctid+'/attribute');
                }).then(function(response) {
                    let data = response.data;
                    Vue.set(ctx, 'attributes', data.attributes);
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!ctx.data[aid]) {
                            Vue.set(ctx.data, aid, {});
                        }
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    Vue.set(ctx, 'selections', data.selections);
                    Vue.set(vm, 'attributesLoaded', true);
                    return vm.$http.get('/api/context/'+cid+'/references');
                }).then(function(response) {
                    let data = response.data;
                    if(data instanceof Array) {
                        data = {};
                    }
                    Vue.set(ctx, 'references', data);
                });
            },
            showMetadata() {

            },
            addNewEntity(entity) {
                if(!entity.name) return;
                if(!entity.type) return;
                let data = {};
                data.name = entity.name;
                data.context_type_id = entity.type.id;
                if(entity.root_context_id) data.root_context_id = entity.root_context_id;
                if(entity.geodata_id) data.geodata_id = entity.geodata_id;

                let tree = this.tree;
                let hideModal = this.hideNewEntityModal;

                this.$http.post('/api/context', data).then(function(response) {
                    tree.push(response.data);
                    hideModal();
                });
            },
            requestAddNewEntity(parent) {
                let modal = this.$modal;
                let entity = this.newEntity;
                this.$http.get('/api/editor/dm/context_type/top').then(function(response) {
                    let selection = response.data;
                    Vue.set(entity, 'name', '');
                    Vue.set(entity, 'type', selection.length == 1 ? selection[0] : {});
                    Vue.set(entity, 'selection', selection);
                    if(parent) {
                        Vue.set(entity, 'root_context_id', parent.id);
                    }
                    modal.show('add-entity-modal');
                });
            },
            hideNewEntityModal() {
                this.newEntity = {};
                this.$modal.hide('add-entity-modal');
            },
            saveEntity(entity) {
                console.log("TODO");
            },
            deleteEntity(entity) {
                let selectedEntity = this.selectedContext;
                let setSelectedElement = this.setSelectedElement;
                let id = entity.id;
                let hideModal = this.hideDeleteEntityModal;
                this.$http.delete('/api/context/'+id).then(function(response) {
                    // if deleted entity is currently selected entity...
                    if(entity == selectedEntity) {
                        // ...unset it
                        setSelectedElement(undefined);
                    }
                    hideModal();
                });
            },
            requestDeleteEntity(entity) {
                this.toDeleteEntity = Object.assign({}, entity);
                this.$modal.show('delete-entity-modal');
            },
            hideDeleteEntityModal() {
                this.toDeleteEntity = {};
                this.$modal.hide('delete-entity-modal');
            },
            duplicateEntity(entity) {
                let duplicate = {
                    name: entity.name,
                    type: {
                        id: entity.context_type_id
                    },
                    root_context_id: entity.root_context_id
                };
                this.addNewEntity(duplicate);
            },
            hasReferenceGroup: function(group) {
                if(!this.selectedContext.references) return false;
                if(!Object.keys(this.selectedContext.references).length) return false;
                if(!this.selectedContext.references[group]) return false;
                let count = Object.keys(this.selectedContext.references[group]).length > 0;
                return count > 0;
            },
            translateLabel(element, label) {
                let value = element[label];
                if(!value) return element;
                let concept = this.concepts[element[label]];
                if(!concept) return element;
                return concept.label;
            }
        },
        data() {
            return {
                selectedContext: {},
                toDeleteEntity: {},
                newEntity: {},
                tab: 'map',
                dataLoaded: false,
                attributesLoaded: false
            }
        },
        computed: {
            isLoaded: function() {
                return this.dataLoaded && this.attributesLoaded;
            },
            hasReferences: function() {
                return this.selectedContext.references && Object.keys(this.selectedContext.references).length;
            }
        }
    }
</script>
