<template>
    <div class="row h-100 of-hidden" v-if="initFinished">
        <div :class="'col-md-'+$getPreference('prefs.columns').left" id="tree-container" class="d-flex flex-column h-100">
            <context-tree
                class="col px-0 scroll-y-auto"
                :on-entity-add="requestAddNewEntity"
                :on-context-menu-duplicate="duplicateEntity"
                :on-context-menu-delete="requestDeleteEntity"
                :roots="roots"
                :selection-callback="onSetSelectedElement">
            </context-tree>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').center" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" class="h-100">
            <router-view class="h-100"
                :bibliography="bibliography">
            </router-view>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').right" id="addon-container" class="d-flex flex-column">
            <ul class="nav nav-tabs">
                <li class="nav-item" v-for="plugin in $getTabPlugins()">
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
            <div class="mt-2 col px-0">
                <keep-alive>
                    <component
                        :context="selectedContext"
                        :context-data-loaded="dataLoaded"
                        :is="activePlugin">
                    </component>
                </keep-alive>
                <div v-show="tab == 'references'" class="h-100 scroll-y-auto">
                    <p class="alert alert-info" v-if="!hasReferences">
                        No references found.
                    </p>
                    <div v-else v-for="(referenceGroup, key) in selectedContext.references" class="mb-2">
                        <h5 class="mb-1">
                            <a href="#" @click="showMetadataForReferenceGroup(referenceGroup)">{{ $translateConcept(key) }}</a>
                        </h5>
                        <div class="list-group">
                            <a class="list-group-item list-group-item-action" v-for="reference in referenceGroup">
                                <blockquote class="blockquote mb-0">
                                    <p class="mb-0">
                                        {{ reference.description }}
                                    </p>
                                    <footer class="blockquote-footer">
                                        {{ reference.bibliography.author }} in <cite :title="reference.bibliography.title">
                                            {{ reference.bibliography.title }} ,{{ reference.bibliography.year }}
                                        </cite>
                                    </footer>
                                </blockquote>
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
                    <button type="submit" class="btn btn-success" :disabled="addEntityDisabled(newEntity)" form="newEntityForm">
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

        <modal name="discard-changes-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Unsaved Changes</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDiscardModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        There are unsaved changes in {{ selectedContext.name }}. Do you really want to continue and discard these changes?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" @click="discardCallback">
                        <i class="fas fa-fw fa-undo"></i> Yes, Discard Changes
                    </button>
                    <button type="button" class="btn btn-success" @click="saveAndContinueCallback">
                        <i class="fas fa-fw fa-check"></i> No, Save and continue
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideDiscardModal">
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
        beforeRouteEnter(to, from, next) {
            let bibliography, roots;
            $http.get('bibliography').then(response => {
                bibliography = response.data;
                return $http.get('context/top');
            }).then(response => {
                roots = response.data;
                next(vm => vm.init(roots, bibliography));
            }).catch(error => {
                $throwError(error);
            });
        },
        mounted() {},
        methods: {
            init(roots, bibliography) {
                this.initFinished = false;
                this.roots = roots;
                this.bibliography = bibliography;
                this.initFinished = true;
            },
            hideDiscardModal() {
                this.$modal.hide('discard-changes-modal');
            },
            onSetSelectedElement(element) {
                // Check if there is a previous selected element
                // and if there is at least one modified attribute
                if(this.selectedContext.id && this.isFormDirty) {
                    this.saveAndContinueCallback = _ => {
                        this.saveEntity(this.selectedContext);
                        this.hideDiscardModal();
                        // TODO wait for async saveEntity
                        this.setSelectedElement(element);
                    };
                    this.discardCallback = _ => {
                        this.resetFlags();
                        this.hideDiscardModal();
                        this.setSelectedElement(element);
                    };
                    this.$modal.show('discard-changes-modal');
                } else {
                    this.setSelectedElement(element);
                }
            },
            setSelectedElement(element) {
                this.attributesLoaded = false;
                this.dataLoaded = false;
                if(!element) {
                    this.selectedContext = {};
                    this.$router.push({
                        name: 'home'
                    });
                } else {
                    this.selectedContext = Object.assign({}, element);
                    // if all extensions are disabled, auto-load references on select
                    if(this.tab == '') {
                        this.tab = 'references';
                    }
                    this.$requestHooks(this.selectedContext);
                    this.$router.push({
                        name: 'contextdetail',
                        params: {
                            id: this.selectedContext.id
                        }
                    });
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
            showMetadataForReferenceGroup(referenceGroup) {
                if(!referenceGroup) return;
                if(!this.selectedContext) return;
                const aid = referenceGroup[0].attribute_id;
                const attribute = this.selectedContext.attributes.find(a => a.id == aid);
                if(!attribute) return;
                // this.showMetadata(attribute); TODO
            },
            addEntityDisabled(entity) {
                return !entity.name || !entity.type;
            },
            addNewEntity(entity) {
                const vm = this;
                if(vm.addEntityDisabled(entity)) return;
                let data = {};
                data.name = entity.name;
                data.context_type_id = entity.type.id;
                if(entity.root_context_id) data.root_context_id = entity.root_context_id;
                if(entity.geodata_id) data.geodata_id = entity.geodata_id;

                vm.$http.post('/context', data).then(function(response) {
                    if (!entity.parent) {
                        vm.roots.push(response.data);
                    }
                    vm.hideNewEntityModal();
                    if (entity.callback) {
                        entity.callback(response.data, entity.parent);
                    }
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            requestAddNewEntity(callback, parent) {
                const vm = this;
                let selection = [];
                if(parent) {
                    selection = vm.$getEntityType(parent.context_type_id).sub_context_types;
                } else {
                    selection = Object.values(vm.$getEntityTypes()).filter(f => f.is_root);
                }
                Vue.set(vm.newEntity, 'name', '');
                Vue.set(vm.newEntity, 'type', selection.length == 1 ? selection[0] : null);
                Vue.set(vm.newEntity, 'selection', selection);
                Vue.set(vm.newEntity, 'callback', callback);
                if(parent) {
                    Vue.set(vm.newEntity, 'root_context_id', parent.id);
                    Vue.set(vm.newEntity, 'parent', parent);
                }
                vm.$modal.show('add-entity-modal');
            },
            hideNewEntityModal() {
                this.newEntity = {};
                this.$modal.hide('add-entity-modal');
            },
            deleteEntity(entity) {
                const vm = this;
                const id = entity.id;
                vm.$http.delete(`/context/${id}`).then(function(response) {
                    // if deleted entity is currently selected entity...
                    if(entity == vm.selectedContext) {
                        // ...unset it
                        vm.setSelectedElement(undefined);
                    }
                    vm.$showToast('Entity deleted', `${entity.name} successfully deleted.`, 'success');
                    if (entity.callback) {
                        entity.callback(entity);
                    }
                    vm.hideDeleteEntityModal();
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            requestDeleteEntity(cb, entity, path) {
                this.toDeleteEntity = Object.assign({}, entity);
                Vue.set(this.toDeleteEntity, 'callback', cb);
                Vue.set(this.toDeleteEntity, 'path', path);
                this.$modal.show('delete-entity-modal');
            },
            hideDeleteEntityModal() {
                this.toDeleteEntity = {};
                this.$modal.hide('delete-entity-modal');
            },
            duplicateEntity(callback, entity, parent) {
                let duplicate = {
                    name: entity.name,
                    type: {
                        id: entity.context_type_id
                    },
                    root_context_id: entity.root_context_id,
                    callback: callback,
                    parent: parent
                };
                this.addNewEntity(duplicate);
            },
            translateLabel(element, label) {
                let value = element[label];
                if(!value) return element;
                return this.$translateConcept(element[label]);
            }
        },
        data() {
            return {
                roots: [],
                bibliography: {},
                initFinished: false,
                selectedContext: {},
                toDeleteEntity: {},
                referenceModal: {},
                newEntity: {},
                saveAndContinueCallback: _ => {},
                discardCallback: _ => {},
                dataLoaded: false,
                defaultKey: undefined,
                plugins: this.$getTabPlugins(),
                activePlugin: ''
            }
        },
        computed: {
            hasReferences: function() {
                return this.selectedContext.references && Object.keys(this.selectedContext.references).length;
            },
            tab: {
                get() {
                    if(this.defaultKey) return this.defaultKey;
                    else if(this.plugins && this.plugins[0]) {
                        this.activePlugin = this.plugins[0].tag;
                        return this.plugins[0].key;
                    } else {
                        return '';
                    }
                },
                set(newValue) {
                    this.defaultKey = newValue;
                }
            }
        }
    }
</script>
