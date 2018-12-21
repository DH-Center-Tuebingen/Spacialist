<template>
    <div class="row h-100 of-hidden" v-if="initFinished">
        <div :class="'col-md-'+$getPreference('prefs.columns').left" id="tree-container" class="d-flex flex-column h-100" v-can="'view_concepts'">
            <entity-tree
                class="col px-0"
                :event-bus="eventBus"
                :selected-entity="selectedEntity">
            </entity-tree>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').center" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" class="h-100" v-can="'view_concepts|view_concept_props'">
            <router-view class="h-100"
                :selected-entity="selectedEntity"
                :bibliography="bibliography"
                :event-bus="eventBus"
                @detail-updated="setDetailDirty"
            >
            </router-view>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').right" id="addon-container" class="d-flex flex-column">
            <ul class="nav nav-tabs">
                <li class="nav-item" v-for="plugin in $getTabPlugins()">
                    <router-link class="nav-link" :class="{active: tab == plugin.key}" :to="{ query: { tab: plugin.key }}" append>
                        <i class="fas fa-fw" :class="plugin.icon"></i> {{ $t(plugin.label) }}
                    </router-link>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" :class="{active: tab == 'references', disabled: !selectedEntity.id}" @click.prevent="setReferencesTab()">
                        <i class="fas fa-fw fa-bookmark"></i> {{ $t('main.entity.references.title') }}
                    </a>
                </li>
            </ul>
            <div class="mt-2 col px-0">
                <keep-alive>
                    <component
                        :selected-entity="selectedEntity"
                        :event-bus="eventBus"
                        :entity-data-loaded="dataLoaded"
                        :is="activePlugin"
                        :params="$route.query"
                        v-on:update:link="updateLink">
                    </component>
                </keep-alive>
                <div v-show="tab == 'references'" class="h-100 scroll-y-auto">
                    <p class="alert alert-info" v-if="!hasReferences">
                        {{ $t('main.entity.references.empty') }}
                    </p>
                    <div v-else v-for="(referenceGroup, key) in selectedEntity.references" class="mb-2">
                        <h5 class="mb-1">
                            <a href="#" @click.prevent="showMetadataForReferenceGroup(referenceGroup)">{{ $translateConcept(key) }}</a>
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
        <discard-changes-modal :name="discardModal"/>
    </div>
</template>

<script>
    import { mapFields } from 'vee-validate';

    export default {
        beforeRouteEnter(to, from, next) {
            let bibliography, entityData;
            $httpQueue.add(() => $http.get('bibliography').then(response => {
                bibliography = response.data;
                if(to.params.id) {
                    return $http.get(`/entity/${to.params.id}`);
                } else {
                    return new Promise(resolve => resolve(null));
                }
            }).then(response => {
                entityData = response ? response.data : null;
                if(to.params.id) {
                    return $http.get(`/entity/${to.params.id}/reference`);
                } else {
                    return new Promise(resolve => resolve(null));
                }
            }).then(response => {
                next(vm => vm.init(bibliography, to.query.tab, entityData, response ? response.data : null));
            }));
        },
        beforeRouteUpdate(to, from, next) {
            if(to.query.tab) {
                this.setTabOrPlugin(to.query.tab);
            }
            let loadNext = () => {
                if(to.params.id) {
                    if(to.params.id != from.params.id) {
                        this.getNewEntity(to.params.id).then(r => {
                            next();
                        });
                    } else {
                        next();
                    }
                } else {
                    this.resetEntity();
                    next();
                }
            }
            if(this.discardState.dirty) {
                let discardAndContinue = () => {
                    loadNext();
                };
                let saveAndContinue = () => {
                    this.discardState.callback(this.selectedEntity).then(loadNext);
                };
                this.$modal.show(this.discardModal, {reference: this.selectedEntity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
            } else {
                loadNext();
            }
        },
        mounted() {},
        methods: {
            getNewEntity(id) {
                return $httpQueue.add(() => $http.get(`/entity/${id}`).then(response => {
                    this.selectedEntity = response.data;
                    return $http.get(`/entity/${id}/reference`);
                }).then(response => {
                    this.selectedEntity.references = response.data;
                }));
            },
            resetEntity() {
                this.selectedEntity = {};
            },
            init(bibliography, openTab, entityData, entityReferences) {
                this.initFinished = false;
                this.bibliography = bibliography;
                if(entityData) {
                    this.selectedEntity = entityData;
                }
                if(entityReferences) {
                    this.selectedEntity.references = entityReferences;
                }
                if(openTab) {
                    this.setTabOrPlugin(openTab);
                }
                this.eventBus.$on('entity-update', this.handleEntityUpdate);
                this.initFinished = true;
                return new Promise(r => r(null));
            },
            setDetailDirty(event) {
                this.discardState.dirty = event.isDirty;
                this.discardState.callback = event.onDiscard;
            },
            setReferencesTab() {
                if(!this.selectedEntity.id) return;
                this.$router.push({
                    append: true,
                    query: {
                        tab: 'references'
                    }
                });
            },
            setTabOrPlugin(key) {
                if(key == 'references') {
                    this.setActiveTab('references');
                } else {
                    const plugins = this.$getTabPlugins();
                    const plugin = plugins.find(p => p.key == key);
                    if(plugin) {
                        this.setActivePlugin(plugin);
                    }
                }
            },
            setActiveTab: function(tab) {
                if(tab == 'references') {
                    if(!this.selectedEntity.id) return;
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
                if(!this.selectedEntity) return;
                const aid = referenceGroup[0].attribute_id;
                this.$router.push({
                    name: 'entityrefs',
                    params: {
                        aid: aid
                    },
                    query: this.$route.query,
                    append: true
                });
            },
            updateLink(geoId, entityId) {
                if(entityId != this.selectedEntity.id) {
                    return;
                }
                this.selectedEntity.geodata_id = geoId;
            },
            handleEntityUpdate(e) {
                if(this.selectedEntity && this.selectedEntity.id == e.entity_id) {
                    switch(e.type) {
                        case 'name':
                            this.selectedEntity.name = e.value;
                        break;
                        default:
                            vm.$throwError({message: `Unknown event type ${e.type} received.`});
                    }
                }
            }
        },
        data() {
            return {
                bibliography: {},
                initFinished: false,
                selectedEntity: {},
                referenceModal: {},
                dataLoaded: false,
                defaultKey: undefined,
                plugins: this.$getTabPlugins(),
                activePlugin: '',
                eventBus: new Vue(),
                discardModal: 'discard-changes-modal',
                discardState: {
                    dirty: false,
                    callback: () => {}
                }
            }
        },
        computed: {
            hasReferences: function() {
                return this.selectedEntity && this.selectedEntity.references && Object.keys(this.selectedEntity.references).length;
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
