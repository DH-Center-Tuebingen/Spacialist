<template>
    <div class="row h-100 of-hidden">
        <div :class="'col-md-'+preferences['prefs.columns'].left" id="tree-container" class="d-flex flex-column h-100">
            <h3>Contexts</h3>
            <div class="d-flex flex-column h-100 col">
                <button type="button" class="btn btn-sm btn-outline-success ml-3 mb-2" @click="requestAddNewEntity()">
                    <i class="fas fa-fw fa-plus"></i> Add new Top-Level Entity
                </button>
                <context-tree
                    class="col scroll-y-auto"
                    :concepts="concepts"
                    :context-types="contextTypes"
                    :on-context-menu-add="requestAddNewEntity"
                    :on-context-menu-duplicate="duplicateEntity"
                    :on-context-menu-delete="requestDeleteEntity"
                    :roots="roots"
                    :selection-callback="setSelectedElement">
                </context-tree>
                <button type="button" class="btn btn-sm btn-outline-success ml-3 mt-2" @click="requestAddNewEntity()">
                    <i class="fas fa-fw fa-plus"></i> Add new Top-Level Entity
                </button>
            </div>
        </div>
        <div :class="'col-md-'+preferences['prefs.columns'].center" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" class="h-100">
            <div v-if="selectedContext.id" class="h-100 d-flex flex-column">
                <div class="d-flex align-items-center justify-content-between">
                    <h1>{{ selectedContext.name }}</h1>
                    <span>
                        <button type="button" class="btn btn-success" :disabled="isFormDirty" @click="saveEntity(selectedContext)">
                            <i class="fas fa-fw fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-danger" @click="requestDeleteEntity(selectedContext)">
                            <i class="fas fa-fw fa-trash"></i> Delete
                        </button>
                    </span>
                </div>
                <attributes class="pt-2 col pl-0 pr-2 scroll-y-auto scroll-x-hidden" v-if="dataLoaded"
                    :attributes="selectedContext.attributes"
                    :concepts="concepts"
                    :dependencies="selectedContext.dependencies"
                    :disable-drag="true"
                    :on-metadata="showMetadata"
                    :metadata-addon="hasReferenceGroup"
                    :selections="selectedContext.selections"
                    :values="selectedContext.data">
                </attributes>
            </div>
            <h1 v-else>Nothing selected</h1>
        </div>
        <div :class="'col-md-'+preferences['prefs.columns'].right" id="addon-container" class="d-flex flex-column">
            <ul class="nav nav-tabs">
                <li class="nav-item" v-for="plugin in plugins.tab">
                    <a class="nav-link" href="#" :class="{active: tab == plugin.key}" @click="setActivePlugin(plugin)">
                        <i class="fas fa-fw" :class="plugin.icon"></i> {{ plugin.label }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" :class="{active: tab == 'references', disabled: !selectedContext.id}" @click="setActiveTab('references')">
                        <i class="fas fa-fw fa-bookmark"></i> References
                    </a>
                </li>
            </ul>
            <div class="mt-2 col">
                <keep-alive>
                    <component
                        :concepts="concepts"
                        :context="selectedContext"
                        :context-data-loaded="dataLoaded"
                        :is="activePlugin"
                        :preferences="preferences">
                    </component>
                </keep-alive>
                <div v-show="tab == 'references'">
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
                    <form id="newEntityForm" name="newEntityForm" role="form" v-on:submit.prevent="addNewEntity(newEntity)">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3" for="name">
                                Name:
                            </label>
                            <div class="col-md-9">
                                <input type="text" id="name" class="form-control" required v-model="newEntity.name" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3" for="type">
                                Type:
                            </label>
                            <multiselect class="col-md-9" style="box-sizing: border-box;"
                                :customLabel="translateLabel"
                                label="thesaurus_url"
                                track-by="id"
                                v-model="newEntity.type"
                                :allowEmpty="false"
                                :closeOnSelect="true"
                                :hideSelected="true"
                                :multiple="false"
                                :options="newEntity.selection">
                            </multiselect>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" form="newEntityForm">
                        <i class="fas fa-fw fa-plus"></i> Add
                    </button>
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

        <modal name="entity-references-modal" width="50%" :scrollable="true" :draggable="true" :resizable="true">
            <div class="modal-content h-100">
                <div class="modal-header">
                    <h5 class="modal-title">References</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideEntityReferenceModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body col col-md-8 offset-md-2 scroll-y-auto" v-if="referenceModal.active">
                    <h4>Certainty</h4>
                    <div class="progress" @click="setCertainty">
                        <div class="progress-bar" role="progressbar" :class="{'bg-danger': referenceModal.attributeValue.possibility <= 25, 'bg-warning': referenceModal.attributeValue.possibility <= 50, 'bg-info': referenceModal.attributeValue.possibility <= 75, 'bg-success': referenceModal.attributeValue.possibility > 75}" :aria-valuenow="referenceModal.attributeValue.possibility" aria-valuemin="0" aria-valuemax="100" :style="{width: referenceModal.attributeValue.possibility+'%'}">
                            <span class="sr-only">
                                {{ referenceModal.attributeValue.possibility }}% certainty
                            </span>
                            {{ referenceModal.attributeValue.possibility }}%
                        </div>
                    </div>
                    <form role="form" class="mt-2" @submit.prevent="updateCertainty">
                        <div class="form-group">
                            <textarea class="form-control" v-model="referenceModal.attributeValue.possibility_description" placeholder="Certainty Comment"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fas fa-fw fa-save"></i> Update Certainty
                            </button>
                        </div>
                    </form>
                    <h4 class="mt-3">References</h4>
                    <table class="table table-hover">
                        <tbody>
                            <tr v-for="reference in referenceModal.references">
                                <td class="text-left py-2">
                                    <h6>{{ reference.bibliography.title }}</h6>
                                    <p class="mb-0">
                                        {{ reference.bibliography.author }} <span class="text-lightgray">{{ reference.bibliography.year}}</span>
                                    </p>
                                </td>
                                <td class="text-right py-2">
                                    <p class="font-weight-light font-italic mb-0" v-if="referenceModal.editReference.id != reference.id">
                                        {{ reference.description }}
                                    </p>
                                    <div class="d-flex" v-else>
                                        <input type="text" class="form-control mr-1" v-model="referenceModal.editReference.description" />
                                        <button type="button" class="btn btn-outline-success mr-1" @click="updateReference(referenceModal.editReference)">
                                            <i class="fas fa-fw fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" @click="cancelEditReference">
                                            <i class="fas fa-fw fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-fw fa-ellipsis-h"></i>
                                        </span>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item" href="#" @click="enableEditReference(reference)">
                                                <i class="fas fa-fw fa-edit text-info"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" @click="deleteReference(reference)">
                                                <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <h5>Add new Reference</h5>
                    <form role="form" @submit.prevent="addReference(referenceModal.newItem)">
                        <div class="row">
                            <div class="col-md-6">
                                <multiselect
                                    label="title"
                                    track-by="id"
                                    v-model="referenceModal.newItem.bibliography"
                                    :closeOnSelect="true"
                                    :hideSelected="true"
                                    :multiple="false"
                                    :options="bibliography">
                                    <template slot="singleLabel" slot-scope="props">
                                        <span class="option__desc">
                                            <span class="option__title">
                                                {{ props.option.title }}
                                            </span>
                                        </span>
                                    </template>
                                    <template slot="option" slot-scope="props">
                                        <div class="option__desc">
                                            <span class="option__title d-block">
                                                {{ props.option.title }}
                                            </span>
                                            <span class="option__small">
                                                {{ props.option.author }}
                                                <span class="text-lightgray">{{ props.option.year}}</span>
                                            </span>
                                        </div>
                                    </template>
                                </multiselect>
                            </div>
                            <div class="col-md-6">
                                <textarea class="form-control" v-model="referenceModal.newItem.description" placeholder="Reference Comment"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-success col-md-12 mt-2" :disabled="addReferenceDisabled">
                            <i class="fas fa-fw fa-plus"></i> Add Reference
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="hideEntityReferenceModal">
                        <i class="fas fa-fw fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import { mapFields } from 'vee-validate';

    export default {
        props: {
            bibliography: {
                required: false,
                type: Array,
                default: []
            },
            concepts: {
                required: false, // TODO required?
                type: Object
            },
            contextTypes: {
                required: false, // TODO required?
                type: Object
            },
            preferences: {
                required: true,
                type: Object
            },
            roots: {
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
                    // if all extensions are disabled, auto-load references on select
                    if(this.tab == '') {
                        this.tab = 'references';
                    }
                    this.$requestHooks(this.selectedContext);
                }
            },
            setActiveTab: function(tab) {
                if(tab == 'references') {
                    if(!this.selectedContext.id) return;
                    this.activePlugin = '';
                }
                this.tab = tab;
            },
            setActivePlugin: function(plugin) {
                this.setActiveTab(plugin.key);
                this.activePlugin = plugin.tag;
            },
            getContextData(elem) {
                let vm = this;
                let cid = elem.id;
                let ctid = elem.context_type_id;
                vm.dataLoaded = false;
                vm.$http.get('/api/context/'+cid+'/data').then(function(response) {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(vm.selectedContext, 'data', response.data);
                    return vm.$http.get('/api/editor/context_type/'+ctid+'/attribute');
                }).then(function(response) {
                    vm.selectedContext.attributes = [];
                    let data = response.data;
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!vm.selectedContext.data[aid]) {
                            let val = {};
                            switch(data.attributes[i].datatype) {
                                case 'dimension':
                                case 'epoch':
                                    val.value = {};
                                    break;
                                case 'table':
                                case 'list':
                                    val.value = [];
                                    break;
                            }
                            Vue.set(vm.selectedContext.data, aid, val);
                        } else {
                            const val = vm.selectedContext.data[aid].value;
                            switch(data.attributes[i].datatype) {
                                case 'date':
                                    const dtVal = new Date(val);
                                    vm.selectedContext.data[aid].value = dtVal;
                                    break;
                            }
                        }
                        vm.selectedContext.attributes.push(data.attributes[i]);
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    if(data.dependencies instanceof Array) {
                        data.dependencies = {};
                    }
                    Vue.set(vm.selectedContext, 'selections', data.selections);
                    Vue.set(vm.selectedContext, 'dependencies', data.dependencies);
                    return vm.$http.get('/api/context/'+cid+'/reference');
                }).then(function(response) {
                    let data = response.data;
                    if(data instanceof Array) {
                        data = {};
                    }
                    Vue.set(vm.selectedContext, 'references', data);
                    Vue.set(vm, 'dataLoaded', true);
                });
            },
            showMetadata(attribute) {
                const refs = this.selectedContext.references[attribute.thesaurus_url];
                Vue.set(this.referenceModal, 'attribute', Object.assign({}, attribute));
                Vue.set(this.referenceModal, 'attributeValue', Object.assign({}, this.selectedContext.data[attribute.id]));
                Vue.set(this.referenceModal, 'references', refs ? refs.slice() : []);
                Vue.set(this.referenceModal, 'active', true);
                Vue.set(this.referenceModal, 'editReference', {});
                Vue.set(this.referenceModal, 'newItem', {bibliography: {}, description: ''});
                this.$modal.show('entity-references-modal');
            },
            hideEntityReferenceModal() {
                this.referenceModal = {};
                this.$modal.hide('entity-references-modal');
            },
            setCertainty(event) {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const currentValue = this.referenceModal.attributeValue.possibility;
                let value = parseInt(clickPos/maxSize*100);
                const diff = Math.abs(value-currentValue);
                if(diff < 10) {
                    if(value > currentValue) {
                        value = parseInt((value+10)/10)*10;
                    } else {
                        value = parseInt(value/10)*10;
                    }
                } else {
                    value = parseInt((value+5)/10)*10;
                }
                Vue.set(this.referenceModal.attributeValue, 'possibility', value);
            },
            updateCertainty() {
                const vm = this;
                const cid = vm.selectedContext.id;
                const aid = vm.referenceModal.attribute.id;
                const oldData = vm.selectedContext.data[aid];
                const newData = vm.referenceModal.attributeValue;
                if(newData.possibility == oldData.possibility && newData.possibility_description == oldData.possibility_description) {
                    return;
                }
                const data = {
                    certainty: newData.possibility,
                    certainty_description: newData.possibility_description
                };
                vm.$http.patch(`/api/context/${cid}/attribute/${aid}`, data).then(function(response) {
                    oldData.possibility = newData.possibility;
                    oldData.possibility_description = newData.possibility_description;
                    const attributeName = vm.$translateConcept(vm.concepts, vm.referenceModal.attribute.thesaurus_url);
                    vm.$showToast('Certainty updated', `Certainty of ${attributeName} successfully set to ${newData.possibility}% (${newData.possibility_description}).`, 'success');
                });
            },
            addReference(item) {
                const vm = this;
                const cid = vm.selectedContext.id;
                const aid = vm.referenceModal.attribute.id;
                if(this.addReferenceDisabled) {
                    return;
                }
                const data = {
                    bibliography_id: item.bibliography.id,
                    description: item.description
                };
                vm.$http.post(`/api/context/${cid}/reference/${aid}`, data).then(function(response) {
                    let refs = vm.selectedContext.references[vm.referenceModal.attribute.thesaurus_url];
                    refs.push(response.data);
                    Vue.set(vm.referenceModal, 'references', refs.slice());
                });
            },
            deleteReference(reference) {
                const vm = this;
                const id = reference.id;
                vm.$http.delete(`/api/reference/${id}`).then(function(response) {
                    let refs = vm.selectedContext.references[vm.referenceModal.attribute.thesaurus_url];
                    const index = refs.findIndex(r => r.id == reference.id);
                    if(index > -1) {
                        refs.splice(index, 1);
                        Vue.set(vm.referenceModal, 'references', refs.slice());
                    }
                });
            },
            enableEditReference(reference) {
                Vue.set(this.referenceModal, 'editReference', Object.assign({}, reference));
            },
            cancelEditReference() {
                Vue.set(this.referenceModal, 'editReference', {});
            },
            updateReference(referenceClone) {
                const vm = this;
                const id = referenceClone.id;
                let refs = vm.selectedContext.references[vm.referenceModal.attribute.thesaurus_url];
                let ref = refs.find(r => r.id == referenceClone.id);
                if(ref.description == referenceClone.description) {
                    return;
                }
                const data = {
                    description: referenceClone.description
                };
                vm.$http.patch(`/api/reference/${id}`, data).then(function(response) {
                    ref.description = referenceClone.description;
                    Vue.set(vm.referenceModal, 'references', refs.slice());
                    vm.cancelEditReference();
                });
            },
            addNewEntity(entity) {
                if(!entity.name) return;
                if(!entity.type) return;
                let data = {};
                data.name = entity.name;
                data.context_type_id = entity.type.id;
                if(entity.root_context_id) data.root_context_id = entity.root_context_id;
                if(entity.geodata_id) data.geodata_id = entity.geodata_id;

                const vm = this;
                vm.$http.post('/api/context', data).then(function(response) {
                    vm.roots.push(response.data);
                    vm.hideNewEntityModal();
                });
            },
            requestAddNewEntity(parent) {
                let vm = this;
                let url;
                if(parent) {
                    url = '/api/editor/dm/context_type/parent/' + parent.id;
                } else {
                    url = '/api/editor/dm/context_type/top';
                }
                vm.$http.get(url).then(function(response) {
                    let selection = response.data;
                    Vue.set(vm.newEntity, 'name', '');
                    Vue.set(vm.newEntity, 'type', selection.length == 1 ? selection[0] : null);
                    Vue.set(vm.newEntity, 'selection', selection);
                    if(parent) {
                        Vue.set(vm.newEntity, 'root_context_id', parent.id);
                    }
                    vm.$modal.show('add-entity-modal');
                });
            },
            hideNewEntityModal() {
                this.newEntity = {};
                this.$modal.hide('add-entity-modal');
            },
            saveEntity(entity) {
                const vm = this;
                let cid = entity.id;
                var patches = [];
                for(let f in vm.fields) {
                    if(vm.fields.hasOwnProperty(f) && f.startsWith('attribute-')) {
                        if(this.fields[f].dirty) {
                            let aid = Number(f.replace(/^attribute-/, ''));
                            let data = entity.data[aid];
                            var patch = {};
                            patch.params = {};
                            patch.params.aid = aid;
                            patch.params.cid = cid;
                            if(data.id) {
                                // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                                patch.params.id = data.id;
                                if(data.value && data.value != '') {
                                    // value is set, therefore it is a replace
                                    patch.op = "replace";
                                    patch.value = data.value;
                                } else {
                                    // value is empty, therefore it is a remove
                                    patch.op = "remove";
                                }
                            } else {
                                // there has been no entry in the database before, therefore it is an add operation
                                if(data.value && data.value != '') {
                                    patch.op = "add";
                                    patch.value = data.value;
                                }
                            }
                            patches.push(patch);
                        }
                    }
                }
                vm.$http.patch('/api/context/'+cid+'/attributes', patches).then(function(response) {
                    vm.resetFlags();
                    vm.$showToast('Entity updated', `Data of ${entity.name} successfully updated.`, 'success');
                });
            },
            deleteEntity(entity) {
                let vm = this;
                let id = entity.id;
                this.$http.delete('/api/context/'+id).then(function(response) {
                    // if deleted entity is currently selected entity...
                    if(entity == vm.selectedContext) {
                        // ...unset it
                        vm.setSelectedElement(undefined);
                    }
                    vm.$showToast('Entity deleted', `${entity.name} successfully deleted.`, 'success');
                    vm.hideDeleteEntityModal();
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
            },
            resetFlags() {
                this.$validator.fields.items.forEach(field => {
                    field.reset();
                });
            }
        },
        data() {
            return {
                selectedContext: {},
                toDeleteEntity: {},
                referenceModal: {},
                newEntity: {},
                dataLoaded: false,
                defaultKey: undefined,
                plugins: {},
                activePlugin: ''
            }
        },
        created() {
            this.$getSpacialistPlugins('plugins');
        },
        computed: {
            addReferenceDisabled: function() {
                return !this.referenceModal.newItem.bibliography.id || this.referenceModal.newItem.description.length == 0;
            },
            hasReferences: function() {
                return this.selectedContext.references && Object.keys(this.selectedContext.references).length;
            },
            tab: {
                get() {
                    if(this.defaultKey) return this.defaultKey;
                    else if(this.plugins.tab && this.plugins.tab[0]) {
                        this.activePlugin = this.plugins.tab[0].tag;
                        return this.plugins.tab[0].key;
                    } else {
                        return '';
                    }
                },
                set(newValue) {
                    this.defaultKey = newValue;
                }
            },
            isFormDirty: function() {
                return !Object.keys(this.fields).some(key => this.fields[key].dirty);
            }
        }
    }
</script>
